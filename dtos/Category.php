<?php
/**
 * 이 파일은 아이모듈 매뉴얼모듈의 일부입니다. (https://www.imodules.io)
 *
 * 매뉴얼 카테고리 구조체를 정의한다.
 *
 * @file /modules/manual/dtos/Category.php
 * @author Arzz <arzz@arzz.com>
 * @license MIT License
 * @modified 2024. 2. 14.
 */
namespace modules\manual\dtos;
class Category
{
    /**
     * @var int $_id 카테고리고유값
     */
    private int $_id;

    /**
     * @var string $_manual_id 매뉴얼 고유값
     */
    private string $_manual_id;

    /**
     * @var string $_title 카테고리명
     */
    private string $_title;

    /**
     * @var bool $_has_version 버전사용여부
     */
    private bool $_has_version;

    /**
     * 카테고리 구조체를 정의한다.
     *
     * @param object $category 카테고리정보
     */
    public function __construct(object $category)
    {
        $this->_id = intval($category->category_id);
        $this->_manual_id = $category->manual_id;
        $this->_title = $category->title;
        $this->_has_version = $category->has_version == 'TRUE';
    }

    /**
     * 고유값을 가져온다.
     *
     * @return int $id
     */
    public function getId(): int
    {
        return $this->_id;
    }

    /**
     * 카테고리명을 가져온다.
     *
     * @return string 카테고리명
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
     * 카테고리 URL 을 가져온다.
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
            // 현재 카테고리 가진 컨텍스트를 검색한다.
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
