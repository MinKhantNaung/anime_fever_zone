@blaze

<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-[#9926f0] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#d122e3] focus:bg-[#9926f0] active:bg-[#d122e3] focus:outline-none focus:ring-2 focus:ring-[#d122e3] focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
