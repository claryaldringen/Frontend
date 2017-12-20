<?php

class CliPresenter extends \Nette\Application\UI\Presenter {

    public function actionDefault() {
        $db = $this->context->getService('dibi.connection');
        $rows = $db->query("SELECT * FROM folders");
        foreach ($rows as $row) {
            print_r($row);
        }
        $this->terminate();
    }

    private function setName($db, $name, $modul, $createUrl = true) {
        $textId = $this->textModel->setText($name, $createUrl);
        $nameId = $db->query("SELECT name_id FROM [name_has_text] WHERE text_id=%i AND language_id=%i", $textId, $this->getLanguageId())->fetchSingle();
        if(empty($nameId)) {
            $db->query("INSERT INTO [name]", array('modul' => $modul));
            $nameId = $this->db->getInsertId();
            $db->query("INSERT INTO [name_has_text]", array('name_id' => $nameId, 'language_id' => $this->getLanguageId(), 'text_id' => $textId));
        }
        return $nameId;
    }
}
