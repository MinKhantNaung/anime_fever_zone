<div class="fixed bottom-4 right-4 sm:bottom-6 sm:right-6 z-[9999]" x-data="{
    isOpen: @entangle('isOpen'),
    messages: [{
        role: 'assistant',
        content: `Hey there! 👋
I’m your AnimeFeverZone AI assistant.
Ask me anything about anime, posts, or the site!`,
    }, ],
    input: '',
    isStreaming: false,
    init() {
        this.scrollToBottom();
        this.$watch('isOpen', (value) => {
            if (value) {
                this.scrollToBottom();
            }
        });
    },
    scrollToBottom() {
        this.$nextTick(() => {
            requestAnimationFrame(() => {
                setTimeout(() => {
                    if (this.$refs.messages) {
                        this.$refs.messages.scrollTop = this.$refs.messages.scrollHeight;
                    }
                }, 10);
            });
        });
    },
    async sendMessage() {
        const message = this.input.trim();

        if (message === '' || this.isStreaming) {
            return;
        }

        this.messages.push({ role: 'user', content: message });
        this.input = '';

        const assistantIndex = this.messages.length;
        this.messages.push({ role: 'assistant', content: '' });
        this.scrollToBottom();

        const history = this.messages.slice(0, -1).slice(-6);
        this.isStreaming = true;

        try {
            const response = await fetch('{{ route('ai-chat.stream') }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.head.querySelector('meta[name=csrf-token]').content,
                },
                body: JSON.stringify({
                    message: message,
                    history: history
                }),
            });

            if (response.status === 422) {
                const errorData = await response.json().catch(() => ({}));
                const errorMessage = errorData?.message ?? 'Validation error occurred.';
                this.messages[assistantIndex].content = `⚠️ ${errorMessage}`;
                this.scrollToBottom();
                return;
            }

            if (!response.ok) {
                throw new Error('Request failed');
            }

            const data = await response.json();
            const rawText = data?.text ?? data?.data?.text ?? '';
            const normalizedText = rawText
                .replace(/\\n/g, '\n')
                .replace(/\n{3,}/g, '\n\n')
                .trim();

            this.messages[assistantIndex].content = normalizedText !== '' ?
                normalizedText :
                'Sorry, I did not get a response.';
            this.scrollToBottom();
        } catch (error) {
            this.messages[assistantIndex].content = 'You’ve reached the usage limit. Please wait a bit and try again.';
            this.scrollToBottom();
        } finally {
            this.isStreaming = false;
        }
    },
}">
    <!-- Chat Window -->
    <div x-show="isOpen" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-95"
        class="mb-4 w-[calc(100vw-2rem)] sm:w-[380px] bg-white rounded-lg shadow-2xl flex flex-col overflow-hidden border border-gray-200"
        style="display: none; max-height: calc(100vh - 100px); height: min(600px, calc(100vh - 100px));">

        <!-- Header -->
        <div
            class="bg-gradient-to-r from-[#9926f0] to-[#d122e3] px-4 sm:px-6 py-3 sm:py-4 flex items-center justify-between">
            <div class="flex items-center gap-2 sm:gap-3">
                <div
                    class="w-8 h-8 sm:w-10 sm:h-10 bg-white rounded-full flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-5 h-5 sm:w-6 sm:h-6 text-[#9926f0]">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-white font-semibold text-base sm:text-lg">AI Assistant</h3>
                    <p class="text-white/80 text-xs hidden sm:block">We're here to help</p>
                </div>
            </div>
            <button @click="isOpen = false"
                class="text-white hover:text-gray-200 transition-colors p-1 rounded-full hover:bg-white/10">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Messages Area -->
        <div class="flex-1 overflow-y-auto p-3 sm:p-4 bg-gray-50 space-y-4" x-ref="messages">
            <template x-for="(message, index) in messages" :key="index">
                <div x-show="message.content !== ''" class="flex items-start gap-3"
                    :class="message.role === 'user' ? 'justify-end' : ''">
                    <div x-show="message.role === 'assistant'"
                        class="w-8 h-8 bg-gradient-to-r from-[#9926f0] to-[#d122e3] rounded-full flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="white" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                        </svg>
                    </div>
                    <div class="max-w-[80%]">
                        <div class="rounded-lg px-4 shadow-sm border text-sm leading-normal whitespace-pre-line"
                            :class="message.role === 'user' ?
                                'bg-[#9926f0] text-white border-transparent rounded-tr-none' :
                                'bg-white text-gray-800 border-gray-200 rounded-tl-none'">
                            <p x-html="message.content"></p>
                        </div>
                    </div>
                </div>
            </template>
            <div x-show="isStreaming" class="text-xs text-gray-500 flex items-center gap-2">
                <span class="inline-flex h-2 w-2 rounded-full bg-[#9926f0] animate-pulse"></span>
                <span>AI is typing…</span>
            </div>
        </div>

        <!-- Input Area -->
        <div class="border-t border-gray-200 bg-white p-3 sm:p-4">
            <div class="flex items-end gap-2">
                <div class="flex-1">
                    <textarea placeholder="Start chatting…" rows="1" x-model="input"
                        @keydown.enter.prevent="if (! $event.shiftKey) { sendMessage(); }"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#9926f0] focus:border-transparent resize-none text-sm"
                        style="max-height: 120px; overflow-y: auto;"></textarea>
                </div>
                <button @click="sendMessage()" :disabled="isStreaming"
                    :class="isStreaming ? 'opacity-60 cursor-not-allowed' : 'hover:opacity-90'"
                    class="bg-gradient-to-r from-[#9926f0] to-[#d122e3] text-white p-3 rounded-lg transition-opacity flex-shrink-0 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                    </svg>
                </button>
            </div>
            <p class="text-xs text-gray-500 mt-2 text-center">
                AI can make mistakes. Verify important information.
            </p>
        </div>
    </div>

    <!-- Floating Chat Button -->
    <button @click="isOpen = !isOpen"
        class="w-12 h-12 sm:w-14 sm:h-14 bg-gradient-to-r from-[#9926f0] to-[#d122e3] text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center hover:scale-110">
        <svg x-show="!isOpen" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
            stroke="currentColor" class="w-5 h-5 sm:w-6 sm:h-6">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
        </svg>
        <svg x-show="isOpen" x-transition xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
            stroke-width="2" stroke="currentColor" class="w-5 h-5 sm:w-6 sm:h-6" style="display: none;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</div>
