<button type="button" onclick="event.stopPropagation()" data-copy-form-link="{{ $url }}" class="wiboost-form-action-button wiboost-form-action-button--copy flex h-10 w-10 shrink-0 items-center justify-center rounded-[1rem] border-2 border-white" title="Salin Tautan">
    <span data-copy-default>@include('partials.wiboost-icon', ['name' => 'note', 'class' => 'h-4 w-4'])</span>
    <span data-copy-success>@include('partials.wiboost-icon', ['name' => 'success', 'class' => 'h-4 w-4'])</span>
    <span class="sr-only">Salin Tautan</span>
</button>
