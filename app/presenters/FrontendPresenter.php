<?php

use Nette\Mail\Message;
use Nette\Application\UI;

class FrontendPresenter extends cms\FrontendPresenter {

	protected function renderDiscography($item, $url)
	{
		parent::renderDiscography($item, $url);
		if(isset($this->data[$item['id']]['album'])) $this['orderForm']->setDefaults(array('album_' . $this->data[$item['id']]['album']->id => 1));
		$this->template->text = $this->context->getService('pageModel')->setLanguage($this->languageId)->getPage($item['id']);
	}

	public function renderHome($url) {
		$item = $this->baseRender($url);
		$this->template->articles = $this->context->getService('articleModel')->setLanguage($this->languageId)->getArticles($item['id']);
	}

	public function renderConcerts($url) {
		$item = $this->baseRender($url);
		$concerts  = array();
		$concs = $this->context->getService('concertModel')->setLanguage($this->languageId)->getConcerts(39);
		foreach($concs as $concert) {
			$date = explode('-', $concert->start_time);
			if(!isset($concerts[$date[0]])) $concerts[$date[0]] = array();
			$concerts[$date[0]][] = $concert;

		}
		$this->template->concerts = $concerts;
		$model = $this->context->getService('articleModel')->setLanguage($this->languageId);
		$this->template->articles = $model->getArticles($item['id']);
		$this->template->length = $model->getSetting($item['id'])->length;
	}


	public function renderContact($url) {
		$item = $this->baseRender($url);
		$this->template->text = $this->context->getService('pageModel')->setLanguage($this->languageId)->getPage($item['id']);
	}

	public function createComponentEmailForm() {
		$form = new UI\Form($this, 'emailForm');
		$form->addText('url')->addRule(UI\Form::BLANK)->setAttribute('class', 'as');
		$form->addText('email', 'Váš e-mail:')
			->addCondition(UI\Form::FILLED)
			->addRule(UI\Form::EMAIL, 'Váše e-mailová adresa není správně zadaná. Zkontrolujte, zdali jste ji zadali ve správném tvaru.');
		$form->addTextArea('message', 'Vaše zpráva:')->setRequired('Vložte prosím text vaší zprávy.');
		$form->addSubmit('send', 'Odeslat');
		$form->onSuccess[] = array($this, 'emailFormSubmitted');
		return $form;
	}

	public function emailFormSubmitted(UI\Form $form) {
		$values = $form->getValues();
		$mail = new Message();
		if(!empty($values['email']))$mail->setFrom($values['email']);
		$mail->setSubject('Vzkaz ze stránek');
		$mail->setBody($values['message']);
		$mail->addAttachment('example.txt', var_export($this->context->getByType('Nette\Http\Request')->getHeaders(), true));
		$mail->addTo('asonance@asonance.cz');
		$mail->addBcc('clary.aldringen@seznam.cz');
		$this->context->getService('mailer')->send($mail);
		$this->flashMessage('Vaše zpráva byla odeslána.');
		$this->redirect('this');
	}

	public function createComponentOrderForm() {
		$form = new UI\Form($this, 'orderForm');
		$this->context->getService('albumModel')->setLanguage($this->languageId);
		$albums = array_merge($this->context->getService('albumModel')->getAlbums(41), $this->context->getService('albumModel')->getAlbums(51));
		$form->addGroup('Alba a zpěvníky');
		foreach($albums as $album) {
			if($album->count) {
				$form->addText('album_' . $album->id, $album->name . ' (' . $album->price . ' Kč):')->setType('number')->setDefaultValue(0);
			}
		}
		$form->addGroup('Kontaktní údaje');
		$form->addText('email', 'Váš e-mail:')
			->addCondition(UI\Form::FILLED)
			->addRule(UI\Form::EMAIL, 'Váše e-mailová adresa není správně zadaná. Zkontrolujte, zdali jste ji zadali ve správném tvaru.');
		$form->addText('name', 'Jméno a příjmení (případně název organizace):')
			->setRequired('Jméno a příjmení, případně název organizace, musí být výplněny.');
		$form->addText('street','Ulice a číslo popisné')->setRequired('Ulice a číslo popisné musí být vyplněny.');
		$form->addText('city','PSČ a Město')->setRequired('PSČ a město musí být vyplněny.');
		$form->addText('phone','Telefon')->setRequired('Telefonní číslo musí být vyplněno.')->addRule(OrderFormRules::PHONE, 'Telefonní číslo nemá správný tvar.');
		$form->addRadioList('post', 'Typ doručení:', array('Zásilka na dobírku (99 Kč, pouze při ceně objednávky do 500 Kč)','Balík na poštu (155 Kč)', 'Balík do ruky (170 Kč)'))->setDefaultValue(0);
		$form->addTextArea('message', 'Poznámka:');
		$form->addSubmit('send', 'Pokračovat');
		$form->setAction($form->getAction() . '#order');
		$form->onSuccess[] = array($this, 'orderFormSubmitted');
		return $form;
	}

	public function orderFormSubmitted(UI\Form $form) {
		$values = $form->getValues();
		$ids = $counts = array();
		foreach($values as $key => $value) {
			if(strpos($key,'album') !== false && $value > 0) {
				$parts = explode('_', $key);
				$ids[] = $parts[1];
				$counts[$parts[1]] = $value;
			}
		}

		$postTypes = array(99,155,170);
		$this->template->post =  $postTypes[$values['post']];
		$totalPrice = $this->template->post;
		$albums = $this->context->getService('albumModel')->setLanguage($this->languageId)->getAlbum($ids);
		foreach($albums as &$album) {
			$album->count = $counts[$album->id];
			$totalPrice += $album->count * $album->price;
		}
		if($totalPrice > 599 && $values['post'] == 0) {
			$this->template->showError = 'Cena objednávky přesahuje 500 Kč. Vyberte prosím jiný způsob doručení.';
			return;
		}
		$this->template->orderAlbums = $albums;
		$this->template->totalPrice = $totalPrice;
		$this->template->name = $values['name'];
		$this->template->street = $values['street'];
		$this->template->city = $values['city'];
		$this->template->message = $values['message'];
		$data = array('albums' => $albums,
			'phone' => $values['phone'],
			'city' => $values['city'],
			'street' => $values['street'],
			'name' => $values['name'],
			'email' => $values['email'],
			'post' => $values['post'],
			'message' => $values['message']);
		$this['recapitulationForm']->setDefaults(array('data' => json_encode($data)));
	}

	public function createComponentRecapitulationForm() {
		$form = new UI\Form($this, 'recapitulationForm');
		$form->addHidden('data');
		$form->addSubmit('send', 'Objednat');
		$form->onSuccess[] = array($this, 'recapitulationFormSubmitted');
	}

	public function recapitulationFormSubmitted(UI\Form $form) {
		$values = $form->getValues();
		$values = json_decode($values['data']);
		$post = array('zásilku na dobírku','balík na poštu', 'balík do ruky');
		$body = "Dobrý den,\n prosím o zaslání alb\n\n";
		foreach($values->albums as $album) {
			$body .= "{$album->count}x {$album->name}\n";
		}
		$body .= "jako {$post[$values->post]} na adresu:\n\n{$values->name}\n{$values->street}\n{$values->city}\nTelefon:{$values->phone}\n\nPoznámka:\n{$values->message}\n\nS pozdravem\n{$values->name}";
		$mail = new Message();
		$mail->setFrom($values->email, $values->name);
		$mail->setSubject('Objednávka alb a zpěvníků');
		$mail->setBody($body);
		$mail->addTo('prodej@asonance.cz');
		$mail->addBcc($values->email, $values->name);
		$this->context->getService('mailer')->send($mail);
		$this->flashMessage('Objednávka byla odeslána. Kopie objednávky byla odeslána také na ' . $values->email);
		$this->redirect('this');
	}

	protected function renderArticles($item, $url)
	{
		$urlParts = explode('/', $url);
		if(count($urlParts) > 1) {
			$parentItem = $this->baseRender($urlParts[0]);
			$this->baseRender($url);
			$this->template->title = $parentItem['text'] . ' - ' . $item['text'];
			$this->template->item['items'] = $parentItem['items'];
		}
		parent::renderArticles($item, $url);
		if (empty($this->data[$item['id']]['articles']) && empty($this->data[$item['id']]['article'])) {
			$url = $item['url'] . '/' . $item['items'][0]['url'];
			$this->redirect('default', ['url' => $url]);
		}
	}
}
