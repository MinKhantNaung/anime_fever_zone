@section('description',
    'Discover the ultimate destination for anime enthusiasts! Dive into insightful reviews, engaging
    discussions, and a vibrant community at Anime Fever Zone. Join us and unleash your passion for anime today!')

    <div class="container mx-auto flex flex-wrap py-6">

        <section class="w-full md:w-1/3 mx-auto flex flex-col px-3 overflow-hidden">

            <h3 class="text-3xl font-extrabold mt-10">Developer</h3>
            <p class="text-lg mt-5">
                Visit our developer's profile on GitHub.
            </p>
            <a href="https://github.com/MinKhantNaung" target="_blank"
                class="text-lg font-extrabold flex items-center mb-10">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                    class="bi bi-github mr-1" viewBox="0 0 16 16">
                    <path
                        d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27s1.36.09 2 .27c1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.01 8.01 0 0 0 16 8c0-4.42-3.58-8-8-8" />
                </svg>
                Min Khant Naung
            </a>

            <div class="my-3">
                <h3 class="text-4xl font-extrabold">Contact Anime Fever Zone</h3>
            </div>

            <form wire:submit='sendToContact'>

                @csrf
                <div class="my-4">
                    <label for="name">Your Name</label>
                    <input wire:model="name" id="name" type="text"
                        class="input input-bordered input-primary w-full mt-1
                        @error('name')
                            border-rose-500
                            focus:border-rose-500
                            focus:ring-0
                            focus:outline-rose-500
                        @enderror"
                        autofocus />
                    @error('name')
                        <x-input-error messages="{{ $message }}" />
                    @enderror
                </div>

                <div class="my-4">
                    <label for="email">Your Email</label>
                    <input wire:model="email" id="email" type="email"
                        class="input input-bordered input-primary w-full mt-1
                        @error('email')
                            border-rose-500
                            focus:border-rose-500
                            focus:ring-0
                            focus:outline-rose-500
                        @enderror" />
                    @error('email')
                        <x-input-error messages="{{ $message }}" />
                    @enderror
                </div>

                <div class="my-4">
                    <label for="category">Your Category</label>
                    <select wire:model="category" id="category"
                        class="select select-primary w-full mt-1
                        @error('category')
                            border-rose-500
                            focus:border-rose-500
                            focus:ring-0
                            focus:outline-rose-500
                        @enderror">
                        <option selected>Pick topic</option>
                        <option value="general">General Information, Feedback, Suggestions</option>
                        <option value="idea">Topic Ideas, Feedback, Corrections or Suggestions</option>
                        <option value="issue">Account Issues or Questions</option>
                        <option value="ads">Display Advertising</option>
                        <option value="copy">Copyrights, Claims, Policy Inquiries</option>
                    </select>
                    @error('category')
                        <x-input-error messages="{{ $message }}" />
                    @enderror
                </div>

                <div class="my-4">
                    <label for="message">Your Message</label>
                    <textarea wire:model="message" id="message"
                        class="textarea textarea-primary w-full mt-1
                        @error('message')
                            border-rose-500
                            focus:border-rose-500
                            focus:ring-0
                            focus:outline-rose-500
                        @enderror"></textarea>
                    @error('message')
                        <x-input-error messages="{{ $message }}" />
                    @enderror
                </div>

                <div class="my-4 text-center">
                    <button type="submit" class="btn btn-secondary text-lg px-5">Send</button>
                </div>
            </form>

        </section>

    </div>

    @push('scripts')
        <script type="application/ld+json">
        {
          "@context": "https://schema.org",
          "@type": "BlogPosting",
          "headline": "Anime Fever Zone",
          "image": "{{ asset('favicon.ico') }}",
          "description": "Explore the latest news, reviews, and discussions on anime and other popular series at Anime Fever Zone. Stay up-to-date
          with the hottest trends and join our vibrant community of anime enthusiasts.",
          "author": {
            "@type": "Person",
            "name": "Anime Fever Zone"
          }
        }
        </script>
    @endpush
