<?php
/**
 * 이 파일은 아이모듈 매뉴얼모듈의 일부입니다. (https://www.imodules.io)
 *
 * 매뉴얼 구조체를 정의한다.
 *
 * @file /modules/manual/dtos/Manual.php
 * @author Arzz <arzz@arzz.com>
 * @license MIT License
 * @modified 2024. 5. 11.
 */
namespace modules\manual\dtos;
class Manual
{
    /**
     * @var string $_id 매뉴얼고유값
     */
    private string $_id;

    /**
     * @var string $title 매뉴얼명
     */
    private string $_title;

    /**
     * @var object $_template 매뉴얼 설정에 따른 템플릿 정보
     */
    private object $_template;

    /**
     * @var \modules\manual\dtos\Category[] $_categories 매뉴얼 카테고리
     */
    private array $_categories;

    /**
     * 매뉴얼 구조체를 정의한다.
     *
     * @param object $manual 매뉴얼정보
     */
    public function __construct(object $manual)
    {
        $this->_id = $manual->manual_id;
        $this->_title = $manual->title;
        $this->_template = json_decode($manual->template);
    }

    /**
     * 고유값을 가져온다.
     *
     * @return string $manual_id
     */
    public function getId(): string
    {
        return $this->_id;
    }

    /**
     * 매뉴얼명을 가져온다.
     *
     * @return string $title
     */
    public function getTitle(): string
    {
        return $this->_title;
    }

    /**
     * 매뉴얼 기본 템플릿설정을 가져온다.
     *
     * @return \Template $template
     */
    public function getTemplateConfigs(): object
    {
        return $this->_template;
    }

    /**
     * 매뉴얼 카테고리를 가져온다.
     *
     * @return Category[] $categories
     */
    public function getCategories(): array
    {
        if (isset($this->_categories) == true) {
            return $this->_categories;
        }

        /**
         * @var \modules\manual\Manual $mManual
         */
        $mManual = \Modules::get('manual');
        $categories = $mManual
            ->db()
            ->select()
            ->from($mManual->table('categories'))
            ->where('manual_id', $this->_id)
            ->orderBy('sort', 'asc')
            ->get();

        $this->_categories = [];
        foreach ($categories as $category) {
            $this->_categories[] = $mManual->getCategory($category);
        }

        return $this->_categories;
    }

    /**
     * 매뉴얼 URL 을 가져온다.
     *
     * @param string|int ...$paths 모듈 URL 에 추가할 내부 경로 (없는 경우 모듈 기본 URL만 가져온다.)
     * @return string $url
     */
    public function getUrl(string|int ...$paths): string
    {
        /**
         * 현재 컨텍스트가 해당 매뉴얼의 컨텍스트인 경우 컨텍스트 URL을 활용한다.
         */
        $context = \Contexts::get();
        if ($context->is('MODULE', 'manual', $this->_id) == true) {
            $url = $context->getUrl();
        } else {
            // 매뉴얼이 포함된 컨텍스트를 검색한다.
            $context = \Contexts::findOne('MODULE', 'manual', $this->_id, [], ['category' => 0], false);
            $url = $context == null ? '/' : $context->getUrl();
        }

        if (count($paths) > 0) {
            $url .= '/' . implode('/', $paths);
        }

        return $url;
    }

    /**
     * 매뉴얼의 접근권한을 가지고 있는지 확인한다.
     *
     * @param ?int $member_id 권한을 확인할 회원고유값 (NULL 인 경우 현재 로그인한 사용자)
     * @return bool $has_permission 권한보유여부
     */
    public function checkPermission(?int $member_id = null): bool
    {
        // @todo: 권한처리
        return true;
    }
}
