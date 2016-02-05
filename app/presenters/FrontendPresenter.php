<?php

use Nette\Application\UI;

class FrontendPresenter extends cms\FrontendPresenter {

	public function createComponentEmailFormCr() {
		$form = new UI\Form($this, 'emailFormCr');
		$form->addText('url')->addRule(~UI\Form::FILLED)->setAttribute('class', 'as');
		$form->addText('email', 'Váš e-mail:')
			->addCondition(UI\Form::FILLED)
			->addRule(UI\Form::EMAIL, 'Váše e-mailová adresa není správně zadaná. Zkontrolujte, zdali jste ji zadali ve správném tvaru.');
		$form->addTextArea('message', 'Vaše zpráva:')->setRequired('Vložte prosím text vaší zprávy.');
		$form->addHidden('type','cr');
		$form->addSubmit('send', 'Odeslat');
		$form->onSuccess[] = array($this, 'emailFormSubmitted');
		return $form;
	}

	public function createComponentEmailFormSk() {
		$form = new UI\Form($this, 'emailFormSk');
		$form->addText('url')->addRule(~UI\Form::FILLED)->setAttribute('class', 'as');
		$form->addText('email', 'Váš e-mail:')
			->addCondition(UI\Form::FILLED)
			->addRule(UI\Form::EMAIL, 'Váše e-mailová adresa není správně zadaná. Zkontrolujte, zdali jste ji zadali ve správném tvaru.');
		$form->addTextArea('message', 'Vaše zpráva:')->setRequired('Vložte prosím text vaší zprávy.');
		$form->addHidden('type','sk');
		$form->addSubmit('send', 'Odeslat');
		$form->onSuccess[] = array($this, 'emailFormSubmitted');
		return $form;
	}

	public function emailFormSubmitted(UI\Form $form) {
		$values = $form->getValues();
		$mails = array('cr' => 'duffy_cavalry@seznam.cz', 'sk' => 'jojozidek@gmail.com');
		$mail = new \Nette\Mail\Message();
		if(!empty($values['email']))$mail->setFrom($values['email']);
		$mail->setSubject('Vzkaz ze stránek');
		$mail->setBody($values['message']);
		$mail->addTo($mails[$values['type']]);
		$mail->addBcc('clary.aldringen@seznam.cz');
		$this->context->getService('mailer')->send($mail);
		$this->flashMessage('Vaše zpráva byla odeslána.');
		$this->redirect('this');
	}

	public function renderContact($url) {
		$this->baseRender($url);
		$this->getImages();
	}

	public function renderDefault($url)
	{
		parent::renderDefault($url);
		$this->getImages();
	}

	private function getImages() {
		$folders = $this->context->getService('galleryModel')->setLanguage($this->languageId)->getFolders(31);
		$this->template->images = array();
		if(isset($folders[0])) {
			foreach ($folders[0]['folders'] as $folder) {
				if ($folder['id'] == 100) {
					foreach ($folder['images'] as $image) {
						$this->template->images[] = $image['file'];
					}
					break;
				}
			}
		}
	}

}
