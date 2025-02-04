<?php

namespace Olz\Faq\Components\OlzFaq;

use Olz\Components\Common\OlzComponent;
use Olz\Components\Page\OlzFooter\OlzFooter;
use Olz\Components\Page\OlzHeader\OlzHeader;
use Olz\Entity\Faq\Question;
use Olz\Entity\Faq\QuestionCategory;
use Olz\Entity\Roles\Role;
use Olz\Repository\Roles\PredefinedRole;
use Olz\Users\Components\OlzUserInfoModal\OlzUserInfoModal;
use Olz\Utils\HttpParams;

/** @extends HttpParams<array{}> */
class OlzFaqParams extends HttpParams {
}

/** @extends OlzComponent<array<string, mixed>> */
class OlzFaq extends OlzComponent {
    public static string $title = "Fragen & Antworten";
    public static string $description = "Antworten auf die wichtigsten Fragen rund um den OL, die OL Zimmerberg und diese Website.";

    public function getHtml(mixed $args): string {
        $this->httpUtils()->validateGetParams(OlzFaqParams::class);
        $entityManager = $this->dbUtils()->getEntityManager();
        $question_repo = $entityManager->getRepository(Question::class);
        $category_repo = $entityManager->getRepository(QuestionCategory::class);

        $role_repo = $entityManager->getRepository(Role::class);
        $nachwuchs_role = $role_repo->getPredefinedRole(PredefinedRole::Nachwuchs);

        $out = OlzHeader::render([
            'title' => self::$title,
            'description' => self::$description,
        ]);

        $nachwuchs_out = '';
        $nachwuchs_assignees = $nachwuchs_role->getUsers();
        foreach ($nachwuchs_assignees as $nachwuchs_assignee) {
            $nachwuchs_out .= OlzUserInfoModal::render([
                'user' => $nachwuchs_assignee,
                'mode' => 'name_picture',
            ]);
        }

        $out .= <<<ZZZZZZZZZZ
            <div class='content-right'>
                <h3>Ansprechperson</h3>
                <div style='padding:0px 10px 0px 10px; text-align:center;'>
                    {$nachwuchs_out}
                </div>
            </div>
            <div class='content-middle'>
                <h1>Fragen & Antworten (FAQ)</h1>
            ZZZZZZZZZZ;

        $categories = $category_repo->findBy(['on_off' => 1], ['position' => 'ASC']);
        foreach ($categories as $category) {
            $out .= "<h2>{$category->getName()}</h2>";
            $questions = $question_repo->findBy(
                ['category' => $category, 'on_off' => 1],
                ['position_within_category' => 'ASC'],
            );
            foreach ($questions as $question) {
                $answer_html = $this->htmlUtils()->renderMarkdown($question->getAnswer());
                $out .= "<h3 id='{$question->getIdent()}'>{$question->getQuestion()}</h3><div>{$answer_html}</div><br />";
            }
        }

        $out .= "</div>";

        $out .= OlzFooter::render();
        return $out;
    }
}
