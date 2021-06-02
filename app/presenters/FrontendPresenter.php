<?php

use Nette\Mail\Message;
use Nette\Application\UI;

class FrontendPresenter extends cms\FrontendPresenter {

    /** @var Nette\Mail\IMailer @inject */
    public $mailer;

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
        $form->addHidden('captcha')->setHtmlId('captcha');
		$form->addSubmit('send', 'Odeslat');
		$form->onSuccess[] = array($this, 'emailFormSubmitted');
		return $form;
	}

	public function emailFormSubmitted(UI\Form $form) {
		$values = $form->getValues();
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = array('secret' =>'6Lfx6XkUAAAAAASelLokXtBuN8KSL0xYup-VuiDl', 'response' => $values['captcha']);

        // use key 'http' even if you send the request to https://...
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            )
        );

        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        if ($result === FALSE) {
            $this->flashMessage('Došlo k chybě a Vaše zpráva nebyla odeslána. Zkuste to prosím později.');
            $this->redirect('this');
            return;
        }

        $captcha = json_decode($result);
        if($captcha->success && $captcha->score > 0.5) {
            $mail = new Message();
            $mail->setFrom('asonance@asonance.cz');
            $mail->setSubject("Vzkaz ze stránek od {$values['email']}");
            $mail->setBody($values['message']);
            $mail->addAttachment('example.txt', var_export($this->context->getByType('Nette\Http\Request')->getHeaders(), true));
            $mail->addTo('asonance@asonance.cz');
            $mail->addBcc('clary.aldringen@seznam.cz');
            $this->mailer->send($mail);
            $this->flashMessage('Vaše zpráva byla odeslána.');
        }
		$this->redirect('this');
	}

	public function createComponentOrderForm() {
		$form = new UI\Form($this, 'orderForm');
		$this->context->getService('albumModel')->setLanguage($this->languageId);
		$albums = $this->context->getService('albumModel')->getAlbums(41);
		$form->addGroup('Alba a zpěvníky');
		foreach($albums as $album) {
			if($album->count) {
				$form->addText('album_' . $album->id, 'CD ' . $album->name . ' (' . $album->price . ' Kč):')->setType('number')->setHtmlAttribute('min', 0)->setDefaultValue(0);
			}
		}

        $books = $this->context->getService('albumModel')->getAlbums(51);
        foreach($books as $book) {
            if($book->count) {
                $form->addText('album_' . $book->id, 'Zpěvník ' . $book->name . ' (' . $book->price . ' Kč):')->setType('number')->setHtmlAttribute('min', 0)->setDefaultValue(0);
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
		$form->addRadioList('post', 'Typ doručení:', array('Doporučená dobírka (Česká pošta): 119-Kč', 'Balíky (Č.p.) pouze při ceně nad 800,-Kč:', '- na poštu 169,-Kč', '- do ruky 189,-Kč'))
            ->setHtmlAttribute('style:', array('', 'display: none', '', ''))
            ->setHtmlAttribute('disabled?', 1)
            ->setDefaultValue(0);
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

		$postTypes = array(119, 0, 169, 189);
		$this->template->post = $postTypes[$values['post']];
		$totalPrice = $this->template->post;
		$albums = $this->context->getService('albumModel')->setLanguage($this->languageId)->getAlbum($ids);
		foreach($albums as &$album) {
			$album->count = $counts[$album->id];
			$totalPrice += $album->count * $album->price;
		}
		if($totalPrice > 919 && $values['post'] == 0) {
			$this->template->showError = 'Cena objednávky přesahuje 800 Kč. Vyberte prosím jiný způsob doručení.';
			return;
		}
        if($totalPrice === $this->template->post) {
            $this->template->showError = 'Není vybraná žádná položka. Vyberte prosím, kolik a kterých položek chcete poslat.';
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
			'message' => $values['message'],
            'totalPrice' => $totalPrice
        );
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
		$post = array('zásilku na dobírku', '', 'balík na poštu', 'balík do ruky');

		$body = "Dobrý den,\nprosím o zaslání alb\n\n";
		$rek = "";
		foreach($values->albums as $album) {
			$rek .= "{$album->count}x {$album->name} ({$album->price} Kč)\n";
		}
		$body .= $rek;
		$body .= "jako {$post[$values->post]} na adresu:\n\n{$values->name}\n{$values->street}\n{$values->city}\nTelefon:{$values->phone}\n\nPoznámka:\n{$values->message}\n\nS pozdravem\n{$values->name}\n({$values->email})";
		$mail = new Message();
		$mail->setFrom('asonance@asonance.cz');
		$mail->setSubject('Objednávka alb a zpěvníků');
		$mail->setBody($body);
		$mail->addTo('prodej@asonance.cz');
		$this->mailer->send($mail);

        $body2 = "Dobrý den,\npotvrzujeme přijetí objednávky alb a zpěvníků. Objednávku vyřídíme zpravidla do týdne.\n\nRekapitulace objednávky:\n";
        $body2 .= $rek;
        $body2 .= "\nZásilka bude doručena jako {$post[$values->post]} s dobírkou na adresu:\n\n{$values->name}\n{$values->street}\n{$values->city}\n\nS přáním pěkného dne\nAsonance";

        $mail2 = new Message();
        $mail2->setFrom('prodej@asonance.cz');
        $mail2->setSubject('Asonance - potvrzení objednávky alb a zpěvníků');
        $mail2->setBody($body2);
        $mail2->addTo($values->email);
        $this->mailer->send($mail2);

		$this->flashMessage('Objednávka byla odeslána. Rekapitulace objednávky byla odeslána na email ' . $values->email);
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
