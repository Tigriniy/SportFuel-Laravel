<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CatalogReferenceController extends Controller
{
    public function index(string $kind): View
    {
        return $this->page($kind, $this->model($kind));
    }

    public function edit(string $kind, int $record): View
    {
        return $this->page($kind, $this->model($kind)->newQuery()->findOrFail($record));
    }

    public function store(Request $request, string $kind): RedirectResponse
    {
        return $this->saveReference($request, $kind, $this->model($kind));
    }

    public function update(Request $request, string $kind, int $record): RedirectResponse
    {
        return $this->saveReference($request, $kind, $this->model($kind)->newQuery()->findOrFail($record));
    }

    public function destroy(string $kind, int $record): RedirectResponse
    {
        $item = $this->model($kind)->newQuery()->findOrFail($record);

        try {
            $item->delete();
        } catch (QueryException $exception) {
            if (! in_array($exception->getCode(), ['23000', '23503'], true)) {
                throw $exception;
            }

            return $this->backToList($kind)->withErrors('Сначала переназначьте товары, которые используют эту запись.');
        }

        return $this->backToList($kind)->with('status', 'Запись удалена.');
    }

    private function saveReference(Request $request, string $kind, Brand|Category $item): RedirectResponse
    {
        $uniqueName = Rule::unique($item->getTable(), 'name');

        if ($item->exists) {
            $uniqueName->ignore($item);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', $uniqueName],
            'description' => ['nullable', 'string', 'max:2000'],
        ], [
            'name.required' => 'Введите название.',
            'name.max' => 'Название должно содержать не более 120 символов.',
            'name.unique' => 'Запись с таким названием уже существует.',
            'description.max' => 'Описание должно содержать не более 2000 символов.',
        ]);

        if (! $item->exists) {
            $data['slug'] = Str::uuid()->toString();
        }

        $item->fill($data)->save();

        return $this->backToList($kind)->with('status', 'Запись сохранена.');
    }

    private function page(string $kind, Brand|Category $item): View
    {
        return view('admin.references', [
            'title' => $kind === 'categories' ? 'Категории' : 'Бренды',
            'kind' => $kind,
            'item' => $item,
            'records' => $item->newQuery()->withCount('products')->orderBy('name')->paginate(20),
        ]);
    }

    private function model(string $kind): Brand|Category
    {
        return match ($kind) {
            'brands' => new Brand,
            'categories' => new Category,
        };
    }

    private function backToList(string $kind): RedirectResponse
    {
        return redirect()->route('admin.references.index', $kind);
    }
}
