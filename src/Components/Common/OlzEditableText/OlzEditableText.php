<?php

namespace Olz\Components\Common\OlzEditableText;

use Olz\Components\Common\OlzComponent;
use Olz\Entity\OlzText;
use Olz\Utils\HtmlUtils;

class OlzEditableText extends OlzComponent {
    public function getHtml($args = []): string {
        $code_href = $this->envUtils()->getCodeHref();
        $data_href = $this->envUtils()->getDataHref();

        $olz_text_id = intval($args['olz_text_id'] ?? 0);
        if ($olz_text_id > 0) {
            $entityManager = $this->dbUtils()->getEntityManager();
            $olz_text_repo = $entityManager->getRepository(OlzText::class);
            $olz_text = $olz_text_repo->findOneBy(['id' => $olz_text_id]);

            $args['permission'] = "olz_text_{$olz_text_id}";
            $args['get_text'] = function () use ($olz_text) {
                return $olz_text ? ($olz_text->getText() ?? '') : '';
            };
            $args['endpoint'] = 'updateOlzText';
            $args['args'] = ['id' => $olz_text_id];
            $args['text_arg'] = 'text';
        }

        $has_access = $this->authUtils()->hasPermission($args['permission'] ?? 'any');

        $get_text_fn = $args['get_text'];
        $raw_markdown = $get_text_fn();

        $html_utils = HtmlUtils::fromEnv();
        $sanitized_html = $html_utils->renderMarkdown($raw_markdown, [
            'html_input' => 'allow', // TODO: Do NOT allow!
        ]);

        if ($has_access) {
            $esc_endpoint = htmlentities(json_encode($args['endpoint']));
            $esc_args = htmlentities(json_encode($args['args']));
            $esc_text_arg = htmlentities(json_encode($args['text_arg']));
            return <<<ZZZZZZZZZZ
            <div class='olz-editable-text'>
                <div class='rendered-html'>
                    <button
                        type='button'
                        onclick='olz.olzEditableTextEdit(this)'
                        class='btn btn-link olz-edit-button'
                    >
                        <img src='{$data_href}assets/icns/edit_16.svg' alt='Bearbeiten' class='noborder' />
                    </button>
                    {$sanitized_html}
                </div>
                <div class='edit-markdown'>
                    <form
                        class='default-form'
                        onsubmit='return olz.olzEditableTextSubmit({$esc_endpoint}, {$esc_args},{$esc_text_arg}, this)'
                    >
                        <textarea name='text'>{$raw_markdown}</textarea>
                        <div class='error-message alert alert-danger' role='alert'></div>
                        <div>
                            <button
                                type='button'
                                class='btn btn-secondary'
                                onclick='olz.olzEditableTextCancel(this)'
                            >
                                Abbrechen
                            </button>
                            <button
                                type='submit'
                                class='btn btn-primary olz-edit-submit'
                            >
                                Speichern
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            ZZZZZZZZZZ;
        }
        return <<<ZZZZZZZZZZ
        <div class='olz-editable-text'>
            {$sanitized_html}
        </div>
        ZZZZZZZZZZ;
    }
}
