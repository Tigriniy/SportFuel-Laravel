<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->is_active, 404);
        abort_if($request->user()->is_blocked, 403);

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'max:2000'],
        ], [
            'rating.*' => 'Выбери оценку от 1 до 5.',
            'comment.required' => 'Напиши отзыв.',
            'comment.max' => 'Отзыв должен содержать не более 2000 символов.',
        ]);

        $review = $product->reviews()->createOrFirst(['user_id' => $request->user()->id], $data);
        $url = route('products.show', $product->slug).'#reviews';

        if (! $review->wasRecentlyCreated) {
            return redirect()->to($url)->withErrors(['review' => 'Ты уже оставил отзыв на этот товар.']);
        }

        return redirect()->to($url)->with('review_status', 'Отзыв отправлен на модерацию.');
    }
}
