<?php

namespace App\Livewire\Admin\Marketing;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Contracts\Communications\AssetStorageInterface;
use App\Models\Communications\Asset;

class AssetsModule extends Component
{
    use WithFileUploads;

    public $upload;
    public $activeFolder = 'all';

    protected $listeners = ['refreshAssets' => '$refresh'];

    public function setFolder($folder)
    {
        $this->activeFolder = $folder;
    }

    public function save(AssetStorageInterface $storage)
    {
        $this->validate([
            'upload' => 'required|file|max:10240', // 10MB Max
        ]);

        $folder = $this->activeFolder === 'all' ? 'templates' : $this->activeFolder;
        
        $path = $storage->store($this->upload, $folder);

        Asset::create([
            'name' => pathinfo($this->upload->getClientOriginalName(), PATHINFO_FILENAME),
            'original_filename' => $this->upload->getClientOriginalName(),
            'path' => $path,
            'storage_disk' => 'public',
            'folder' => $folder,
            'file_size' => $this->upload->getSize(),
            'mime_type' => $this->upload->getMimeType(),
            // 'dimensions' => ... (can be added later via Image processing)
        ]);

        $this->reset('upload');
        session()->flash('message', 'Asset uploaded successfully.');
    }

    public function deleteAsset($id, AssetStorageInterface $storage)
    {
        $asset = Asset::findOrFail($id);
        
        // Delete from storage
        $storage->delete($asset->path);
        
        // Delete from DB
        $asset->delete();
        
        session()->flash('message', 'Asset deleted.');
    }

    public function getFoldersProperty()
    {
        return ['Brand', 'Festival', 'Deals', 'Products', 'Templates'];
    }

    public function getAssetsProperty()
    {
        $query = Asset::query()->latest();
        
        if ($this->activeFolder !== 'all') {
            $query->where('folder', strtolower($this->activeFolder));
        }

        return $query->get();
    }

    public function render()
    {
        return view('livewire.admin.marketing.assets-module')
            ->layout('admin.layout');
    }
}
