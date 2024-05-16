<?php
/**
 * 이 파일은 아이모듈 매뉴얼모듈의 일부입니다. (https://www.imodules.io)
 *
 * 모듈관리자 클래스를 정의한다.
 *
 * @file /modules/manual/admin/Manual.php
 * @author Arzz <arzz@arzz.com>
 * @license MIT License
 * @modified 2024. 5. 11.
 */
namespace modules\manual\admin;
class Manual extends \modules\admin\admin\Component
{
    /**
     * 관리자 컨텍스트 목록을 가져온다.
     *
     * @return \modules\admin\dtos\Context[] $contexts
     */
    public function getContexts(): array
    {
        $contexts = [];

        if ($this->hasPermission('manuals') == true) {
            $contexts[] = \modules\admin\dtos\Context::init($this)
                ->setContext('manuals')
                ->setTitle($this->getText('admin.contexts.manuals'), 'xi xi-tagged-book');
        }

        return $contexts;
    }

    /**
     * 현재 모듈의 관리자 컨텍스트를 가져온다.
     *
     * @param string $path 컨텍스트 경로
     * @return string $html
     */
    public function getContext(string $path): string
    {
        switch ($path) {
            case 'manuals':
                \Html::script($this->getBase() . '/scripts/contexts/manuals.js');
                break;
        }

        return '';
    }

    /**
     * 현재 컴포넌트의 관리자 권한범위를 가져온다.
     *
     * @return \modules\admin\dtos\Scope[] $scopes
     */
    public function getScopes(): array
    {
        $scopes = [];

        $scopes[] = \modules\admin\dtos\Scope::init($this)
            ->setScope('manuals', $this->getText('admin.scopes.title'))
            ->addChild('manuals', $this->getText('admin.scopes.manuals'))
            ->addChild('categories', $this->getText('admin.scopes.categories'))
            ->addChild('contents', $this->getText('admin.scopes.contents'))
            ->addChild('pages', $this->getText('admin.scopes.pages'));

        return $this->setScopes($scopes);
    }

    /**
     * 매뉴얼 목차를 가져온다.
     *
     * @param string $manual_id 매뉴얼고유값
     * @param string $category_id 분류고유값
     * @param ?string $version 버전명
     * @param ?string $parent_id 상위목차고유값
     */
    public function getContents(
        string $manual_id,
        string $category_id,
        int $limit = 3,
        int $depth = 0,
        ?string $version = null,
        ?string $parent_id = null
    ): array {
        $contents = $this->db()
            ->select()
            ->from($this->table('contents'))
            ->where('manual_id', $manual_id)
            ->where('category_id', $category_id)
            ->where('parent_id', $parent_id)
            ->orderBy('sort', 'asc')
            ->get();
        foreach ($contents as &$content) {
            if ($depth < $limit) {
                $children = $this->getContents(
                    $manual_id,
                    $category_id,
                    $limit,
                    $depth + 1,
                    $version,
                    $content->content_id
                );
                if (count($children) > 0) {
                    $content->children = $children;
                }
            }
        }

        return $contents;
    }

    /**
     * 문서를 삭제한다.
     *
     * @param string $content_id 목차고유값
     * @param int $start_version 문서최소값
     * @return bool $success
     */
    public function deleteDocument(string $content_id, int $start_version): bool
    {
        $document = $this->db()
            ->select()
            ->from($this->table('documents'))
            ->where('content_id', $content_id)
            ->where('start_version', $start_version)
            ->getOne();
        if ($document === null) {
            return false;
        }

        /**
         * @var \modules\attachment\Attachment $mAttachment
         */
        $mAttachment = \Modules::get('attachment');
        $attachments = $mAttachment->getAttachments(
            $this->getComponent(),
            'manual.document',
            $content_id . '.' . $start_version
        );
        foreach ($attachments as $attachment) {
            $mAttachment->deleteFile($attachment->getId());
        }

        $this->db()
            ->delete($this->table('documents'))
            ->where('content_id', $content_id)
            ->where('start_version', $start_version)
            ->execute();

        return true;
    }
}
