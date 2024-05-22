<?php
/**
 * 이 파일은 아이모듈 매뉴얼모듈의 일부입니다. (https://www.imodules.io)
 *
 * 문서를 저장한다.
 *
 * @file /modules/manual/processes/document.post.php
 * @author Arzz <arzz@arzz.com>
 * @license MIT License
 * @modified 2024. 5. 11.
 *
 * @var \modules\manual\Manual $me
 */
if (defined('__IM_PROCESS__') == false) {
    exit();
}

/**
 * 관리자권한이 존재하는지 확인한다.
 */
if ($me->getAdmin()->checkPermission('manuals', ['documents']) == false) {
    $results->success = false;
    $results->message = $me->getErrorText('FORBIDDEN');
    return;
}

$manual_id = Request::get('manual_id', true);
$category_id = Request::get('category_id', true);
$content_id = Request::get('content_id', true);
$start_version = Request::get('start_version');

$document = null;
if ($start_version !== null) {
    $document = $me
        ->db()
        ->select()
        ->from($me->table('documents'))
        ->where('content_id', $content_id)
        ->where('start_version', $start_version)
        ->getOne();
    if ($document == null) {
        $results->success = false;
        $results->message = 'NOT_FOUND_DATA';
        return;
    }
}

$category = $me->getCategory($manual_id, $category_id);
if ($category === null) {
    $results->success = false;
    $results->message = 'NOT_FOUND_DATA';
    return;
}

$errors = [];
$all_version = $category->hasVersion() == false || Input::get('all_version') ? true : false;

if ($all_version == true) {
    $start_version = -1;
    $end_version = -1;

    $check = $me
        ->db()
        ->select()
        ->from($me->table('documents'))
        ->where('content_id', $content_id);
    if ($document !== null) {
        $check->where('start_version', $document->start_version, '!=');
    }
    if ($check->has() == true) {
        $errors['start_version'] = $me->getErrorText('DUPLICATED_ALL_VERSION');
    }
} else {
    $start_version = $me->getVersionToInt(Input::get('start_version') ?? '');
    if ($start_version === 0) {
        $errors['start_version'] = $me->getErrorText('INVALID_VERSION');
    }

    $end_version = $me->getVersionToInt(Input::get('end_version') ?? '');
    if ($end_version === 0) {
        $errors['end_version'] = $me->getErrorText('INVALID_VERSION');
    }

    $check = $me
        ->db()
        ->select()
        ->from($me->table('documents'))
        ->where('content_id', $content_id);
    if ($document !== null) {
        $check->where('start_version', $document->start_version, '!=');
    }

    if ($start_version > 0 && $end_version > 0) {
        $start_version_duplicated = $check
            ->copy()
            ->where('start_version', $start_version, '<=')
            ->where('end_version', $start_version, '>')
            ->getOne();
        if ($start_version_duplicated !== null) {
            $errors['start_version'] = $me->getErrorText('DUPLICATED_VERSION', [
                'start_version' => $me->getIntToVersion($start_version_duplicated->start_version),
                'end_version' => $me->getIntToVersion($start_version_duplicated->end_version),
            ]);
        }

        $end_version_duplicated = $check
            ->where('start_version', $end_version, '<=')
            ->where('end_version', $end_version, '>')
            ->getOne();
        if ($start_version_duplicated !== null) {
            $errors['end_version'] = $me->getErrorText('DUPLICATED_VERSION', [
                'start_version' => $me->getIntToVersion($start_version_duplicated->start_version),
                'end_version' => $me->getIntToVersion($start_version_duplicated->end_version),
            ]);
        }
    }
}

if (
    isset($errors['start_version']) == true &&
    isset($errors['end_version']) == true &&
    $errors['start_version'] == $errors['end_version']
) {
    $errors['end_version'] = null;
}

/**
 * @var \modules\wysiwyg\Wysiwyg $mWysiwyg
 */
$mWysiwyg = Modules::get('wysiwyg');
$content = $mWysiwyg->getEditorContent(
    Input::get('content'),
    $me,
    'manual.document',
    $content_id . '.' . $document?->start_version ?? 'new'
);

/**
 * @var \modules\member\Member $mMember
 */
$mMember = Modules::get('member');

if (count($errors) == 0) {
    $insert = [];
    $insert['manual_id'] = $manual_id;
    $insert['category_id'] = $category_id;
    $insert['content_id'] = $content_id;
    $insert['start_version'] = $start_version;
    $insert['end_version'] = $end_version;
    $insert['content'] = Format::toJson($content->getJson());
    $insert['search'] = '';
    $insert['member_id'] = $mMember->getLogged();
    $insert['updated_at'] = time();

    /**
     * @var \modules\attachment\Attachment $mAttachment
     */
    $mAttachment = Modules::get('attachment');

    if ($document === null) {
        $me->db()
            ->insert($me->table('documents'), $insert)
            ->execute();
    } else {
        $me->db()
            ->update($me->table('documents'), $insert)
            ->where('content_id', $document->content_id)
            ->where('start_version', $document->start_version)
            ->execute();

        if ($document->start_version !== $start_version) {
            $attachments = $mAttachment->getAttachments(
                $me,
                'document',
                $document->content_id . '.' . $document->start_version
            );
            foreach ($attachments as $attachment) {
                if (in_array($attachment->getId(), $content->getAttachments()) == false) {
                    $mAttachment->deleteFile($attachment->getId());
                }
            }
        }
    }

    /**
     * @var \modules\attachment\Attachment $mAttachment
     */
    $mAttachment->moveFiles($content->getAttachments(), $me, 'document', $content_id . '.' . $start_version, true);

    $results->success = true;
} else {
    $results->success = false;
    $results->errors = $errors;
}
