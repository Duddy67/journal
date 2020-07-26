<?php 
use Codalia\Journal\Models\ExtraField;

// Redirects all the orderings views except for the reorder one.

Route::get('backend/codalia/journal/orderings', function() {
    return redirect('backend/codalia/journal/articles');
});

Route::get('backend/codalia/journal/orderings/create', function() {
    return redirect('backend/codalia/journal/articles');
});

Route::get('backend/codalia/journal/orderings/update/{id}', function() {
    return redirect('backend/codalia/journal/articles');
});

Route::get('backend/codalia/journal/orderings/preview/{id}', function() {
    return redirect('backend/codalia/journal/articles');
});

Route::get('backend/codalia/journal/extrafields/json/{id}/{token}', function($id, $token) {
    if(\Session::token() !== $token) {
	return redirect('404');
    }

    echo json_encode(ExtraField::getMultiValues($id));

})->middleware('web');
