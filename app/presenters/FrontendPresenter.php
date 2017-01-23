<?php

use Nette\Application\UI;

class FrontendPresenter extends cms\FrontendPresenter {

	protected function baseRender($url)
	{
		$item = parent::baseRender($url);
		$this->template->languageId = $this->languageId;
		return $item;
	}

	public function createComponentEmailFormCr() {
		$label = array(36 => 'Váš e-mail:', 40 => 'Your e-mail:');
		$error = array(
			36 => 'Vaše e-mailová adresa není správně zadaná. Zkontrolujte, zdali jste ji zadali ve správném tvaru.',
			40 => 'Your e-mail address is not valid.'
		);
		$form = new UI\Form($this, 'emailFormCr');
		$form->addText('url')->addRule(~UI\Form::FILLED)->setAttribute('class', 'as');
		$form->addText('email', $label[$this->languageId])
			->addCondition(UI\Form::FILLED)
			->addRule(UI\Form::EMAIL, $error[$this->languageId]);
		$label = array(36 => 'Vaše zpráva:', 40 => 'Your message:');
		$error = array(
			36 => 'Vložte prosím text vaší zprávy.',
			40 => 'Please, fill your message.'
		);
		$form->addTextArea('message', $label[$this->languageId])->setRequired($error[$this->languageId]);
		$form->addHidden('type','cr');
		$label = array(36 => 'Odeslat', 40 => 'Send');
		$form->addSubmit('send', $label[$this->languageId]);
		$form->onSuccess[] = array($this, 'emailFormSubmitted');
		return $form;
	}

	public function createComponentEmailFormSk() {
		$label = array(36 => 'Váš e-mail:', 40 => 'Your e-mail:');
		$error = array(
			36 => 'Vaše e-mailová adresa není správně zadaná. Zkontrolujte, zdali jste ji zadali ve správném tvaru.',
			40 => 'Your e-mail address is not valid.'
		);
		$form = new UI\Form($this, 'emailFormSk');
		$form->addText('url')->addRule(~UI\Form::FILLED)->setAttribute('class', 'as');
		$form->addText('email', $label[$this->languageId])
			->addCondition(UI\Form::FILLED)
			->addRule(UI\Form::EMAIL, $error[$this->languageId]);
		$label = array(36 => 'Vaše zpráva:', 40 => 'Your message:');
		$error = array(
			36 => 'Vložte prosím text vaší zprávy.',
			40 => 'Please, fill your message.'
		);
		$form->addTextArea('message', $label[$this->languageId])->setRequired($error[$this->languageId]);
		$form->addHidden('type','sk');
		$label = array(36 => 'Odeslat', 40 => 'Send');
		$form->addSubmit('send', $label[$this->languageId]);
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
		$folders = $this->context->getService('galleryModel')->setLanguage(36)->getFolders(31);
		$this->template->images = array();
		if(isset($folders[0])) {
			foreach ($folders[0]['folders'] as $folder) {
				if ($folder['id'] == 100 && !empty($folder['images'])) {
					foreach ($folder['images'] as $image) {
						$this->template->images[] = $image['file'];
					}
					break;
				}
			}
		}
	}

	public function handleChangeLanguage() {
		if($this->languageId == 40) {
			$this->languageId = 36;
		} else {
			$this->languageId = 40;
		}
		$this->getSession('cms')->languageId = $this->languageId;
		$this->redirect('default');
	}

	protected function renderGallery($item, $url)
	{
		$oldLanguageId = $this->languageId;
		$this->languageId = 36;
		$out = parent::renderGallery($item, $url);
		$this->languageId = $oldLanguageId;
		return $out;
	}

}
