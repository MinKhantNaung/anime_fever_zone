<div class="max-w-md mx-auto bg-white shadow p-4 rounded">
    <h2 class="text-xl font-semibold mb-3">Add New Video</h2>

    @if (session()->has('success'))
        <div class="bg-green-100 text-green-800 p-2 rounded mb-3">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit.prevent="save">
        <div class="mb-3">
            <label class="block font-medium mb-1">Title</label>
            <input type="text" wire:model="title" class="w-full border rounded p-2">
            @error('title')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3">
            <label class="block font-medium mb-1">YouTube URL</label>
            <input type="text" wire:model="youtube_url" class="w-full border rounded p-2"
                placeholder="https://www.youtube.com/watch?v=XXXX">
            @error('youtube_url')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">
            Save Video
        </button>
    </form>




    <div>
        <h2 class="text-2xl font-semibold mb-4">{{ $video->title }}</h2>

        <video id="video-player" class="video-js vjs-default-skin" controls preload="auto" width="640" height="360">
        </video>

        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Initialize video player when DOM is ready
                    if (window.videojs && document.getElementById('video-player')) {
                        const player = window.videojs('video-player', {
                            techOrder: ['youtube'],
                            sources: [{
                                type: 'video/youtube',
                                src: 'https://www.youtube.com/watch?v={{ $video->youtube_id }}'
                            }]
                        });
                    }
                });

                // Re-initialize when Livewire updates the component
                document.addEventListener('livewire:init', () => {
                    Livewire.hook('morph.updated', () => {
                        if (window.videojs && document.getElementById('video-player')) {
                            const existingPlayer = window.videojs.getPlayer('video-player');
                            if (existingPlayer) {
                                existingPlayer.dispose();
                            }
                            const player = window.videojs('video-player', {
                                techOrder: ['youtube'],
                                sources: [{
                                    type: 'video/youtube',
                                    src: 'https://www.youtube.com/watch?v={{ $video->youtube_id }}'
                                }]
                            });
                        }
                    });
                });
            </script>
        @endpush
    </div>
</div>
