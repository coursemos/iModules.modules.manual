<?php
/**
 * 이 파일은 아이모듈 매뉴얼모듈의 일부입니다. (https://www.imodules.io)
 *
 * 문서 구조체를 정의한다.
 *
 * @file /modules/manual/dtos/Document.php
 * @author Arzz <arzz@arzz.com>
 * @license MIT License
 * @modified 2024. 5. 21.
 */
namespace modules\manual\dtos;
class Document
{
    /**
     * @var object $_document 문서정보
     */
    private object $_document;
    /**
     * @var string $_manual_id 매뉴얼고유값
     */
    private string $_manual_id;

    /**
     * @var string $_category_id 분류고유값
     */
    private string $_category_id;
    /**
     * @var string $_id 목차고유값
     */
    private string $_content_id;

    /**
     * @var string $_content 내용
     */
    private string $_content;

    /**
     * 문서 구조체를 정의한다.
     *
     * @param object $document 문서정보
     */
    public function __construct(object $document)
    {
        $this->_document = $document;
        $this->_manual_id = $document->manual_id;
        $this->_category_id = $document->category_id;
        $this->_content_id = $document->content_id;
    }

    /**
     * 문서 내용을 가져온다.
     *
     * @return string $content
     */
    public function getContent(): string
    {
        if (isset($this->_content) == false) {
            /**
             * @var \modules\wysiwyg\Wysiwyg $mWysiwyg
             */
            $mWysiwyg = \Modules::get('wysiwyg');
            $viewer = $mWysiwyg->getViewerContent($this->_document->content);

            $this->_content = $viewer->getContent();
        }

        return \Html::element('div', ['data-role' => 'wysiwyg-content'], $this->_content);
    }
}
