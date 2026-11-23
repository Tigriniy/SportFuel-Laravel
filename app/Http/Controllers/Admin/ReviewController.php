<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    public function index(): View
    {
        return view('admin.reviews', [
            'reviews' => Review::with(['product', 'user'])
                ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [Review::PENDING])
                ->latest('id')->paginate(20),
        ]);
    }

    public function update(Request $request, Review $review): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in([Review::APPROVED, Review::REJECTED])],
        ], ['status.in' => 'Выбери, опубликовать или отклонить отзыв.']);

        $review->update(['status' => $data['status'], 'moderated_at' => now()]);

        return redirect()->route('admin.reviews.index')->with('status', 'Статус отзыва сохранён.');
    }
}
