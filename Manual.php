<?php
/**
 * 이 파일은 아이모듈 매뉴얼모듈의 일부입니다. (https://www.imodules.io)
 *
 * 매뉴얼모듈 클래스를 정의한다.
 *
 * @file /modules/manual/Manual.php
 * @author Arzz <arzz@arzz.com>
 * @license MIT License
 * @modified 2024. 5. 11.
 */
namespace modules\manual;
class Manual extends \Module
{
    /**
     * @var \modules\manual\dtos\Manual[] $_manuals 매뉴얼 정보를 저장한다.
     */
    private static array $_manuals = [];

    /**
     * @var \modules\manual\dtos\Category[] $_categories 카테고리 정보를 저장한다.
     */
    private static array $_categories = [];

    /**
     * 모듈의 컨텍스트 목록을 가져온다.
     *
     * @return array $contexts 컨텍스트목록
     */
    public function getContexts(): array
    {
        $contexts = [];
        $manuals = $this->db()
            ->select(['manual_id', 'title'])
            ->from($this->table('manuals'))
            ->orderBy('title', 'ASC')
            ->get();
        foreach ($manuals as $manual) {
            $contexts[] = ['name' => $manual->manual_id, 'title' => $manual->title . '(' . $manual->manual_id . ')'];
        }
        return $contexts;
    }

    /**
     * 모듈의 컨텍스트 설정필드를 가져온다.
     *
     * @return array $context 컨텍스트명
     * @return array $fields 설정필드목록
     */
    public function getContextConfigsFields(string $context): array
    {
        $fields = [];
        $template = [
            'name' => 'template',
            'label' => $this->getText('template'),
            'type' => 'template',
            'component' => [
                'type' => 'module',
                'name' => $this->getName(),
                'use_default' => true,
            ],
            'value' => '#',
        ];
        $fields[] = $template;

        return $fields;
    }

    /**
     * 모듈 컨텍스트의 콘텐츠를 가져온다.
     *
     * @param string $manual_id 매뉴얼고유값
     * @param ?object $configs 컨텍스트 설정
     * @return string $html
     */
    public function getContext(string $manual_id, ?object $configs = null): string
    {
        $manual = $this->getManual($manual_id);
        if ($manual === null) {
            return \ErrorHandler::get($this->error('NOT_FOUND_MANUAL', $manual_id));
        }

        /**
         * 컨텍스트 템플릿을 설정한다.
         */
        if (isset($configs?->template) == true && $configs->template->name !== '#') {
            $this->setTemplate($configs->template);
        } else {
            $this->setTemplate($manual->getTemplateConfigs());
        }

        $content = '';

        return $this->getTemplate()->getLayout($content);
    }

    /**
     * 매뉴얼 정보를 가져온다.
     *
     * @param string $manual_id 매뉴얼고유값
     * @return \modules\manual\dtos\Manual $manual 매뉴얼정보
     */
    public function getManual(string $manual_id): \modules\manual\dtos\Manual
    {
        if (isset(self::$_manuals[$manual_id]) == true) {
            return self::$_manuals[$manual_id];
        }

        $manual = $this->db()
            ->select()
            ->from($this->table('manuals'))
            ->where('manual_id', $manual_id)
            ->getOne();
        if ($manual === null) {
            \ErrorHandler::print($this->error('NOT_FOUND_BOARD', $manual_id));
        }

        self::$_manuals[$manual_id] = new \modules\manual\dtos\Manual($manual, $this);
        return self::$_manuals[$manual_id];
    }

    /**
     * 카테고리 정보를 가져온다.
     *
     * @param string $manual_id 매뉴얼고유값
     * @param string $category_id 카테고리고유값
     * @return ?\modules\manual\dtos\Category $category 카테고리정보
     */
    public function getCategory(string $manual_id, string $category_id): ?\modules\manual\dtos\Category
    {
        if (isset(self::$_categories[$manual_id . '@' . $category_id]) == true) {
            return self::$_categories[$manual_id . '@' . $category_id];
        }

        if (isset($category) == false) {
            $category = $this->db()
                ->select()
                ->from($this->table('categories'))
                ->where('manual_id', $manual_id)
                ->where('category_id', $category_id)
                ->getOne();
        }

        if ($category === null) {
            self::$_categories[$manual_id . '@' . $category_id] = null;
        } else {
            self::$_categories[$manual_id . '@' . $category_id] = new \modules\manual\dtos\Category($category);
        }

        return self::$_categories[$manual_id . '@' . $category_id];
    }

    /**
     * 버전을 숫자로 변환하여 가져온다.
     *
     * @param string $version 버전
     * @return int $version 버전
     */
    public function getVersionToInt(string $version): int
    {
        if (preg_match('/^([0-9]+)\.([0-9]{1,3})$/', $version, $match) == true) {
            return $match[1] * 1000 + $match[2];
        } else {
            return 0;
        }
    }

    /**
     * 버전을 숫자로 변환하여 가져온다.
     *
     * @param string $version 버전
     * @return int $version 버전
     */
    public function getIntToVersion(int $version): string
    {
        if ($version < 1000) {
            return '0.0';
        }

        return floor($version / 1000) . '.' . $version % 1000;
    }

    /**
     * 특수한 에러코드의 경우 에러데이터를 현재 클래스에서 처리하여 에러클래스로 전달한다.
     *
     * @param string $code 에러코드
     * @param ?string $message 에러메시지
     * @param ?object $details 에러와 관련된 추가정보
     * @return \ErrorData $error
     */
    public function error(string $code, ?string $message = null, ?object $details = null): \ErrorData
    {
        switch ($code) {
            /**
             * 매뉴얼이 존재하지 않는 경우
             */
            case 'NOT_FOUND_MANUAL':
                $error = \ErrorHandler::data($code, $this);
                $error->message = $this->getErrorText('NOT_FOUND_MANUAL', ['manual_id' => $message]);
                return $error;

            /**
             * URL 경로가 존재하지 않는 경우
             */
            case 'NOT_FOUND_CONTEXT':
                $error = \ErrorHandler::data($code, $this);
                $error->message = $this->getErrorText('NOT_FOUND_CONTEXT');
                $error->suffix = $message;
                return $error;

            /**
             * 권한이 부족한 경우, 로그인이 되어 있지 않을 경우, 로그인관련 에러메시지를 표시하고
             * 그렇지 않은 경우 권한이 부족하다는 에러메시지를 표시한다.
             */
            case 'FORBIDDEN':
                $error = \ErrorHandler::data($code, $this);
                /**
                 * @var ModuleMember $mMember
                 */
                $mMember = \Modules::get('member');
                if ($mMember->isLogged() == true) {
                    $error->prefix = $this->getErrorText('FORBIDDEN');
                    $error->message = $this->getErrorText('FORBIDDEN_DETAIL', [
                        'code' => $this->getErrorText('FORBIDDEN_CODE/' . $message),
                    ]);
                } else {
                    $error->prefix = $this->getErrorText('REQUIRED_LOGIN');
                }
                return $error;

            default:
                return parent::error($code, $message, $details);
        }
    }
}
