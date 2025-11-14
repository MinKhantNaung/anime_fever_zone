@section('meta-og')
    <link rel="stylesheet" href="{{ asset('assets/trix/trix.min.css') }}">
@endsection

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
                <div wire:ignore>
                    <input id="trix-editor-content" type="hidden" name="description" value="{{ $description }}">
                    <trix-editor input="trix-editor-content" placeholder="Enter video description..."></trix-editor>
                </div>
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

            <div class="mb-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" wire:model="is_trending" class="checkbox checkbox-primary">
                    <span class="text-black">Trending</span>
                </label>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="btn btn-primary">Save Video</button>
                <a href="{{ route('blogger.videos.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
</div>

@push('scripts')
    <script src="{{ asset('assets/trix/trix.umd.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const trixEditor = document.getElementById('trix-editor-content');
            if (trixEditor) {
                document.addEventListener('trix-blur', function(event) {
                    @this.set('description', trixEditor.getAttribute('value'))
                });

                document.addEventListener('trix-change', function(event) {
                    @this.set('description', trixEditor.getAttribute('value'))
                });
            }
        });
    </script>
@endpush
