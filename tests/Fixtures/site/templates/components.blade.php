<x-title class="font-bold">{{ $page->title()->html() }}</x-title>
<x-container class="mb-4">Inside container</x-container>
<x-container.full class="mb-4">Inside full container</x-container.full>
<x-alert>
    <x-slot:title>Success!</x-slot:title>
    Great job!
</x-alert>
<x-dynamic-component component="placeholder" class="dynamic" />
<x-with-class />
<x-with-class message="Hi Kirby" />
<x-layout>Hi Friend!</x-layout>
