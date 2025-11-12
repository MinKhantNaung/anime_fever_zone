<div class="container mx-auto flex flex-wrap py-6">
    <section class="w-full md:w-2/3 flex flex-col items-center px-3">
        <h1 class="text-2xl font-bold mb-4 text-black">Add New Video</h1>

        <form wire:submit.prevent="save" class="w-full max-w-2xl bg-white shadow-md rounded-lg p-6">
            <div class="mb-4">
                <label class="block font-medium mb-2 text-black">Title</label>
                <input type="text" wire:model="title" class="w-full border rounded p-2">
                @error('title')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-2 text-black">Description</label>
                <textarea wire:model="description" rows="4" class="w-full border rounded p-2" placeholder="Enter video description..."></textarea>
                @error('description')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-2 text-black">YouTube URL</label>
                <input type="text" wire:model="youtube_url" class="w-full border rounded p-2"
                    placeholder="https://www.youtube.com/watch?v=XXXX">
                @error('youtube_url')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" wire:model="is_publish" class="checkbox checkbox-primary">
                    <span class="text-black">Publish</span>
                </label>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="btn btn-primary">Save Video</button>
                <a href="{{ route('blogger.videos.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
</div>
