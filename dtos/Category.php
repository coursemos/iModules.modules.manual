<?php
/**
 * 이 파일은 아이모듈 매뉴얼모듈의 일부입니다. (https://www.imodules.io)
 *
 * 분류 구조체를 정의한다.
 *
 * @file /modules/manual/dtos/Category.php
 * @author Arzz <arzz@arzz.com>
 * @license MIT License
 * @modified 2024. 5. 21.
 */
namespace modules\manual\dtos;
class Category
{
    /**
     * @var object $_category 카테고리정보
     */
    private object $_category;

    /**
     * @var string $_id 분류고유값
     */
    private string $_id;

    /**
     * @var string $_manual_id 매뉴얼고유값
     */
    private string $_manual_id;

    /**
     * @var string $_title 분류명
     */
    private string $_title;

    /**
     * @var \modules\manual\dtos\Content[] $_contents 목차
     */
    private array $_contents;

    /**
     * @var bool $_has_version 버전사용여부
     */
    private bool $_has_version;

    /**
     * @var int[] $_versions 버전목록
     */
    private array $_versions;

    /**
     * 분류 구조체를 정의한다.
     *
     * @param object $category 분류정보
     */
    public function __construct(object $category)
    {
        $this->_category = $category;
        $this->_id = $category->category_id;
        $this->_manual_id = $category->manual_id;
        $this->_title = $category->title;
        $this->_has_version = $category->has_version == 'TRUE';
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
     * 버전사용여부를 가져온다.
     *
     * @return bool $has_version
     */
    public function hasVersion(): bool
    {
        return $this->_has_version;
    }

    /**
     * 전체버전을 가져온다.
     *
     * @return int[] $versions
     */
    public function getVersions(): array
    {
        if (isset($this->_versions) == false) {
            if ($this->hasVersion() == false) {
                $this->_versions = [];
            } else {
                $versions = array_values(array_filter(explode("\n", $this->_category->versions ?? '')));
                sort($versions);
                foreach ($versions as &$version) {
                    $version = intval($version);
                }
                $this->_versions = $versions;
            }
        }

        return $this->_versions;
    }

    /**
     * 최신버전을 가져온다.
     *
     * @return int $latest_version
     */
    public function getLatestVersion(): int
    {
        $versions = $this->getVersions();
        return count($versions) == 0 ? -1 : end($versions);
    }

    /**
     * 분류의 목차를 가져온다.
     *
     * @return \modules\manual\dtos\Content[] $contents
     */
    public function getContents(): array
    {
        if (isset($this->_contents) == false) {
            /**
             * @var \modules\manual\Manual $mManual
             */
            $mManual = \Modules::get('manual');

            $contents = $mManual
                ->db()
                ->select()
                ->from($mManual->table('contents'))
                ->where('manual_id', $this->_manual_id)
                ->where('category_id', $this->_id)
                ->where('parent_id', null)
                ->orderBy('sort', 'asc')
                ->get();

            foreach ($contents as &$content) {
                $content = new \modules\manual\dtos\Content($content);
            }

            $this->_contents = $contents;
        }

        return $this->_contents;
    }

    /**
     * 분류에 속한 특정 목차를 가져온다.
     *
     * @param string $content_id description
     * @return \modules\manual\dtos\Content $content
     */
    public function getContent(string $content_id): ?\modules\manual\dtos\Content
    {
        /**
         * @var \modules\manual\Manual $mManual
         */
        $mManual = \Modules::get('manual');

        $content = $mManual
            ->db()
            ->select()
            ->from($mManual->table('contents'))
            ->where('content_id', $content_id)
            ->where('manual_id', $this->_manual_id)
            ->where('category_id', $this->_id)
            ->getOne();

        if ($content === null) {
            return null;
        }

        return new \modules\manual\dtos\Content($content);
    }

    /**
     * 분류 URL 을 가져온다.
     *
     * @return string $url
     */
    public function getUrl(): string
    {
        /**
         * 현재 컨텍스트가 해당 매뉴얼의 컨텍스트인 경우 컨텍스트 URL을 활용한다.
         */
        $context = \Contexts::get();
        if ($context->is('MODULE', 'manual', $this->_id) == true) {
            $url = $context->getUrl();
            $url .= '/' . $this->_id;
        } else {
            // 현재 분류 가진 컨텍스트를 검색한다.
            $context = \Contexts::findOne('MODULE', 'manual', $this->_id, ['category' => $this->_id], [], false);
            if ($context == null) {
                /**
                 * @var \modules\manual\Manual $mManual
                 */
                $mManual = \Modules::get('manual');
                $url = $mManual->getManual($this->_manual_id)->getUrl();
                $url .= '/' . $this->_id;
            } else {
                $url = $context == null ? '/' : $context->getUrl();
            }
        }

        return $url;
    }
}
