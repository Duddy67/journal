<?php 
use Codalia\Journal\Models\Field;
use Codalia\Journal\Models\Article;

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

Route::get('backend/codalia/journal/fields/json/{id}/{token}', function($id, $token) {
    if(\Session::token() !== $token) {
	return redirect('404');
    }

    // Prepares the response.
    $response = ['success' => true, 'message' => '', 'data' => ''];

    try {
	$data = Field::getOptions($id);
	$response['data'] = $data;
    }
    catch (Exception $e) {
	$response['success'] = false;
	$response['message'] = $e->getMessage();
    }

    echo json_encode($response);

})->middleware('web');

Route::get('backend/codalia/journal/articles/json/{id}/{group_id}/{token}', function($id, $groupId, $token) {
    if(\Session::token() !== $token) {
	return redirect('404');
    }

    // Prepares the response.
    $response = ['success' => true, 'message' => '', 'data' => ''];

    try {
	$data = Article::getFields($groupId, $id);
	$response['data'] = $data;
    }
    catch (Exception $e) {
	$response['success'] = false;
	$response['message'] = $e->getMessage();
    }

    echo json_encode($response);

})->middleware('web');
