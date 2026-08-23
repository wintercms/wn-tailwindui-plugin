<!--
    @TODO:
    - Implement as QA item, perhaps provided by the Winter.Search plugin or built into
    backend / system module. Long term goal is a Command Palette type experience
-->
<form class="w-full flex md:ml-0 hidden" action="#" method="GET">
    <!-- @TODO: Needs translation -->
    <label for="search-field" class="sr-only">Search</label>
    <div class="relative w-full text-gray-400 focus-within:text-gray-600">
        <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
        <input
            id="search-field"
            class="block w-full h-full pl-8 pr-3 py-2 border-transparent text-gray-900 placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-0 focus:border-transparent sm:text-sm"
            placeholder="Search"
            type="search"
            name="search"
        >
    </div>
</form>
