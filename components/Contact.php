<?php namespace Codalia\Journal\Components;

use Cms\Classes\ComponentBase;
use Input;
use Mail;
use Validator;
use Redirect;

class Contact extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'codalia.journal::lang.settings.contact_title',
            'description' => 'codalia.journal::lang.settings.contact_description'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onSend()
    {
        $validator = Validator::make(
	    ['name' => Input::get('name'), 'email' => Input::get('email') ],
	    ['name' => 'required|min:2', 'email' => 'required|email'
	]);

	if ($validator->fails()) {
	    return Redirect::back()->withErrors($validator);
	}
	else {
	    $vars = ['name' => Input::get('name'), 'email' => Input::get('email'), 'content' => Input::get('content')];
	    Mail::send('codalia.journal::mail.message', $vars, function($message) {

	    $message->to('lucas.sanner+admin@gmail.com', 'Admin Person');
	    $message->subject('New message from contact form.');
	    });
	}
    }
}
