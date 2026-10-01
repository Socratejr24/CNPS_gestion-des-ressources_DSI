<div class="toast-container"
     x-data="toastManager()"
     x-init="init()"
     x-cloak>

    <template x-for="toast in toasts" :key="toast.id">
        <div class="toast"
             :class="[
                 'toast--' + toast.type,
                 toast.leaving ? 'toast--leaving' : ''
             ]">

            <svg xmlns="http://www.w3.org/2000/svg"
                 viewBox="0 0 24 24"
                 fill="none"
                 stroke="currentColor"
                 stroke-width="2"
                 stroke-linecap="round"
                 stroke-linejoin="round"
                 class="toast__icon"
                 x-html="iconFor(toast.type)">
            </svg>

            <div class="toast__content">
                <p class="toast__title" x-text="toast.title"></p>
                <p class="toast__text" x-show="toast.text" x-text="toast.text"></p>
            </div>

            <button type="button"
                    class="toast__close"
                    @click="remove(toast.id)"
                    aria-label="Fermer">
                <svg xmlns="http://www.w3.org/2000/svg"
                     width="14" height="14"
                     viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="2"
                     stroke-linecap="round"
                     stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
    </template>

</div>