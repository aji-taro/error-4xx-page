<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// 任意のステータスコードで abort() して、表示されるエラーページを確認する
Route::get('/debug_error/{code}', fn (int $code) => abort($code));
