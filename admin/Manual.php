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
            ->addChild('documents', $this->getText('admin.scopes.documents'));

        return $this->setScopes($scopes);
    }

    /**
     * 매뉴얼 목차를 가져온다.
     *
     * @param string $manual_id 매뉴얼고유값
     * @param string $category_id 분류고유값
     * @param bool $is_root 최상위 그룹 여부
     * @param ?string $version 버전명
     * @param int $depth 단계
     * @param ?string $parent_id 상위목차고유값
     */
    public function getContents(
        string $manual_id,
        string $category_id,
        bool $is_root,
        ?int $version = null,
        int $depth = 0,
        ?string $parent_id = null
    ): array {
        $limit = $is_root == true ? 2 : 3;

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
                    $is_root,
                    $version,
                    $depth + 1,
                    $content->content_id
                );

                if (count($children) > 0) {
                    $content->children = $children;
                }

                if ($is_root == false) {
                    $documents = $this->db()
                        ->select(['count(*) as documents', 'sum(hits) as hits'])
                        ->from($this->table('documents'))
                        ->where('content_id', $content->content_id);
                    if ($version !== -1) {
                        $documents->where('start_version', $version, '<=')->where('end_version', $version, '>');
                    }
                    $documents = $documents->groupBy('content_id')->getOne();
                    $content->documents = $documents?->documents ?? 0;
                    $content->hits = $documents?->hits ?? 0;
                }
            }
        }

        return $contents;
    }

    /**
     * 매뉴얼을 삭제한다.
     *
     * @param string $manual_id 매뉴얼고유값
     * @return bool $success
     */
    public function deleteManual(string $manual_id): bool
    {
        $manual = $this->db()
            ->select()
            ->from($this->table('manuals'))
            ->where('manual_id', $manual_id)
            ->getOne();
        if ($manual === null) {
            return false;
        }

        $categories = $this->db()
            ->select()
            ->from($this->table('categories'))
            ->where('manual_id', $manual_id)
            ->get();
        foreach ($categories as $category) {
            $this->deleteCategory($category->manual_id, $category->category_id);
        }

        $this->db()
            ->delete($this->table('manuals'))
            ->where('manual_id', $manual_id)
            ->execute();

        return true;
    }

    /**
     * 분류를 삭제한다.
     *
     * @param string $manual_id 매뉴얼고유값
     * @param string $category_id 분류고유값
     * @return bool $success
     */
    public function deleteCategory(string $manual_id, string $category_id): bool
    {
        $category = $this->db()
            ->select()
            ->from($this->table('categories'))
            ->where('manual_id', $manual_id)
            ->where('category_id', $category_id)
            ->getOne();
        if ($category === null) {
            return false;
        }

        $contents = $this->db()
            ->select()
            ->from($this->table('contents'))
            ->where('manual_id', $manual_id)
            ->where('category_id', $category_id)
            ->where('parent_id', null)
            ->get();
        foreach ($contents as $content) {
            $this->deleteContent($content->content_id);
        }

        $this->db()
            ->delete($this->table('categories'))
            ->where('manual_id', $manual_id)
            ->where('category_id', $category_id)
            ->execute();

        return true;
    }

    /**
     * 목차를 삭제한다.
     *
     * @param string $content_id 목차고유값
     * @return bool $success
     */
    public function deleteContent(string $content_id): bool
    {
        $content = $this->db()
            ->select()
            ->from($this->table('contents'))
            ->where('content_id', $content_id)
            ->getOne();
        if ($content === null) {
            return false;
        }

        /**
         * 자식목차를 삭제한다.
         */
        $children = $this->db()
            ->select()
            ->from($this->table('contents'))
            ->where('parent_id', $content_id)
            ->get();
        foreach ($children as $child) {
            $this->deleteContent($child->content_id);
        }

        $documents = $this->db()
            ->select()
            ->from($this->table('documents'))
            ->where('content_id', $content_id)
            ->get();
        foreach ($documents as $document) {
            $this->deleteDocument($document->content_id, $document->start_version);
        }

        $this->db()
            ->delete($this->table('contents'))
            ->where('content_id', $content_id)
            ->execute();

        return true;
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
