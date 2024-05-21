<?php
/**
 * 이 파일은 아이모듈 매뉴얼모듈의 일부입니다. (https://www.imodules.io)
 *
 * 목차 구조체를 정의한다.
 *
 * @file /modules/manual/dtos/Content.php
 * @author Arzz <arzz@arzz.com>
 * @license MIT License
 * @modified 2024. 5. 21.
 */
namespace modules\manual\dtos;
class Content
{
    /**
     * @var string $_id 목차고유값
     */
    private string $_id;

    /**
     * @var string $_manual_id 매뉴얼고유값
     */
    private string $_manual_id;

    /**
     * @var string $_category_id 분류고유값
     */
    private string $_category_id;

    /**
     * @var string $_title 목차명
     */
    private string $_title;

    /**
     * @var \modules\manual\dtos\Content[] $_children 자식목차
     */
    private array $_children;

    /**
     * 목차 구조체를 정의한다.
     *
     * @param object $content 목차정보
     */
    public function __construct(object $content)
    {
        $this->_id = $content->content_id;
        $this->_manual_id = $content->manual_id;
        $this->_category_id = $content->category_id;
        $this->_title = $content->title;
    }

    /**
     * 고유값을 가져온다.
     *
     * @return string $id
     */
    public function getId(): string
    {
        return $this->_id;
    }

    /**
     * 분류명을 가져온다.
     *
     * @return string 분류명
     */
    public function getTitle(): string
    {
        return $this->_title;
    }

    /**
     * 자식목차를 가져온다.
     *
     * @return \modules\manual\dtos\Content[] $children
     */
    public function getChildren(): array
    {
        if (isset($this->_children) == false) {
            /**
             * @var \modules\manual\Manual $mManual
             */
            $mManual = \Modules::get('manual');
            $children = $mManual
                ->db()
                ->select()
                ->from($mManual->table('contents'))
                ->where('manual_id', $this->_manual_id)
                ->where('category_id', $this->_category_id)
                ->where('parent_id', $this->_id)
                ->orderBy('sort', 'asc')
                ->get();

            foreach ($children as &$child) {
                $child = new \modules\manual\dtos\Content($child);
            }

            $this->_children = $children;
        }

        return $this->_children;
    }

    /**
     * 특정 버전에 해당하는 문서를 가져온다.
     *
     * @param int $version
     */
    public function getDocument(int $version = -1): ?\modules\manual\dtos\Document
    {
        /**
         * @var \modules\manual\Manual $mManual
         */
        $mManual = \Modules::get('manual');
        $document = $mManual
            ->db()
            ->select()
            ->from($mManual->table('documents'))
            ->where('content_id', $this->_id);

        if ($version > -1) {
            $document->where('start_version', $version, '<=')->where('end_version', $version, '>');
        }

        $document = $document->getOne();
        if ($document === null) {
            return null;
        }

        return new \modules\manual\dtos\Document($document);
    }

    /**
     * 자식 목차가 존재하는지 확인한다.
     *
     * @return bool $has_child
     */
    public function hasChild(): bool
    {
        return count($this->getChildren()) > 0;
    }

    /**
     * 특정버전에 대해 문서가 존재하는지 확인한다.
     *
     * @param int $version 확인할 버전
     * @return bool $has_document
     */
    public function hasDocument(int $version = -1): bool
    {
        /**
         * @var \modules\manual\Manual $mManual
         */
        $mManual = \Modules::get('manual');
        $documents = $mManual
            ->db()
            ->select()
            ->from($mManual->table('documents'))
            ->where('content_id', $this->_id);
        if ($version > -1) {
            $documents->where('start_version', $version, '<=')->where('end_version', $version, '>');
        }

        return $documents->has();
    }

    /**
     * 특정버전에 대해 목차가 보이는지 확인한다.
     *
     * @param int $version 확인할 버전
     * @return bool $is_visible
     */
    public function isVisible(int $version = -1): bool
    {
        if ($this->hasDocument($version) == true) {
            return true;
        }

        foreach ($this->getChildren() as $child) {
            if ($child->isVisible($version) == true) {
                return true;
            }
        }

        return false;
    }

    /**
     * 분류 URL 을 가져온다.
     *
     * @return string $url
     */
    public function getUrl(int $version = -1): string
    {
        /**
         * @var \modules\manual\Manual $mManual
         */
        $mManual = \Modules::get('manual');
        $category = $mManual->getCategory($this->_manual_id, $this->_category_id);
        $url = $category?->getUrl() ?? '';
        $url .= '/' . $this->_id;

        if ($version > -1) {
            $url .= '/' . $mManual->getIntToVersion($version);
        }

        return $url;
    }
}
