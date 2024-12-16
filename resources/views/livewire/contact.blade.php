@section('description',
    'Discover the ultimate destination for anime enthusiasts! Dive into insightful reviews, engaging
    discussions, and a vibrant community at Anime Fever Zone. Join us and unleash your passion for anime today!')

    <div class="container mx-auto flex flex-wrap py-6">

        <section class="w-full md:w-1/3 mx-auto flex flex-col px-3 overflow-hidden">

            <h3 class="text-3xl font-extrabold mt-10">Developer</h3>
            <p class="text-lg mt-5">
                Connect with our developer on LinkedIn.
            </p>
            <a href="https://www.linkedin.com/in/min-khant-naung-22226a25a/" target="_blank"
                class="text-lg font-extrabold flex items-center mb-10">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                    class="bi bi-linkedin mr-1" viewBox="0 0 16 16">
                    <path
                        d="M0 1.146C0 .513.526 0 1.175 0h13.65C15.474 0 16 .513 16 1.146v13.708c0 .633-.526 1.146-1.175 1.146H1.175C.526 16 0 15.487 0 14.854zm4.943 12.248V6.169H2.542v7.225zm-1.2-8.212c.837 0 1.358-.554 1.358-1.248-.015-.709-.52-1.248-1.342-1.248S2.4 3.226 2.4 3.934c0 .694.521 1.248 1.327 1.248zm4.908 8.212V9.359c0-.216.016-.432.08-.586.173-.431.568-.878 1.232-.878.869 0 1.216.662 1.216 1.634v3.865h2.401V9.25c0-2.22-1.184-3.252-2.764-3.252-1.274 0-1.845.7-2.165 1.193v.025h-.016l.016-.025V6.169h-2.4c.03.678 0 7.225 0 7.225z" />
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
