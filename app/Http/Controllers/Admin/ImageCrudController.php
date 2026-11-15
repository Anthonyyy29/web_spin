<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Dasar untuk menu admin yang menyimpan data beserta satu gambar (crew, podcast, news, sponsor).
 */
abstract class ImageCrudController extends Controller
{
    protected int $maxImageKb = 2048;

    /**
     * @return class-string<Model>
     */
    abstract protected function modelClass(): string;

    /**
     * Nama menu yang dicatat di audit log.
     */
    abstract protected function auditName(): string;

    /**
     * Aturan validasi untuk isian selain gambar.
     *
     * @return array<string, mixed>
     */
    protected function fieldRules(): array
    {
        return [];
    }

    /**
     * Ubah isian yang sudah valid sebelum disimpan.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function prepare(array $data): array
    {
        return $data;
    }

    public function save(Request $request): RedirectResponse
    {
        $id = $request->input('id');

        $validator = Validator::make($request->all(), [
            ...$this->fieldRules(),
            'image' => [$id ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png', 'max:'.$this->maxImageKb],
        ]);

        if ($validator->fails()) {
            return back()->with('error', $validator->errors()->first());
        }

        $class = $this->modelClass();
        $model = $id ? $class::find($id) : new $class;

        if ($model === null) {
            return back()->with('error', 'Data tidak ditemukan.');
        }

        $data = $this->prepare(collect($validator->validated())->except('image')->all());

        if ($request->hasFile('image')) {
            $this->deleteUpload($model->image);
            $data['image'] = $this->storeUpload($request->file('image'));
        }

        $model->fill($data)->save();
        AuditLog::record($this->auditName(), $id ? 'UPDATE' : 'CREATE');

        return back()->with('success', 'Data berhasil disimpan.');
    }

    public function delete(int $id): RedirectResponse
    {
        $model = $this->modelClass()::find($id);

        if ($model === null) {
            return back()->with('error', 'Data tidak ditemukan.');
        }

        $this->deleteUpload($model->image);
        $model->delete();
        AuditLog::record($this->auditName(), 'DELETE');

        return back()->with('success', 'Data berhasil dihapus.');
    }
}
