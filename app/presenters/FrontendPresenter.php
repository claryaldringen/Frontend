<?php

use Nette\Mail\Message;
use Nette\Application\UI;

class FrontendPresenter extends cms\FrontendPresenter {

    /** @var Nette\Mail\IMailer @inject */
    public $mailer;

	public function renderContact($url) {
		$item = $this->baseRender($url);
		$this->template->text = $this->context->getService('pageModel')->setLanguage($this->languageId)->getPage($item['id']);
	}

	public function createComponentEmailForm() {
		$form = new UI\Form($this, 'emailForm');
		$form->addText('url')->addRule(UI\Form::BLANK)->setAttribute('class', 'as');
		$form->addText('email', 'Váš e-mail:')
			->addCondition(UI\Form::FILLED)
			->addRule(UI\Form::EMAIL, 'Vaše e-mailová adresa není správně zadaná. Zkontrolujte, zdali jste ji zadali ve správném tvaru.');
		$form->addTextArea('message', 'Vaše zpráva:')->setRequired('Vložte prosím text vaší zprávy.');
		$form->addSubmit('send', 'Odeslat');
		$form->onSuccess[] = array($this, 'emailFormSubmitted');
		return $form;
	}

	public function emailFormSubmitted(UI\Form $form) {
		$values = $form->getValues();
		$mail = new Message();
		$mail->setFrom('info@hradeckydvur.net');
		$mail->setSubject('Vzkaz ze stránek od '.$values['email']);
		$mail->setBody($values['message']);
		$mail->addAttachment('example.txt', var_export($this->context->getByType('Nette\Http\Request')->getHeaders(), true));
		$mail->addTo('admindvur@seznam.cz');
		$mail->addBcc('clary.aldringen@seznam.cz');
		$this->mailer->send($mail);
		$this->flashMessage('Vaše zpráva byla odeslána.');
		$this->redirect('this');
	}

	public function renderArticles($item, $url)
	{
		if(!empty($item['items'])) {
			$model = $this->context->getService('articleModel')->setLanguage($this->languageId);
			foreach ($item['items'] as $subItem) {
				$articles = $model->getArticles($subItem['id']);
				if(!empty($articles)) {
					$this->data[$item['id']]['articles'][] = $articles[0];
				}
			}
		} else {
			parent::renderArticles($item, $url);
		}
	}
}
