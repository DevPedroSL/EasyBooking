<div id="confirm-modal" class="fixed inset-0 z-[100] hidden" aria-hidden="true">
  <div class="absolute inset-0 bg-slate-950/50" data-confirm-close></div>
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="w-[min(100%,28rem)] rounded-3xl border-4 border-red-600 bg-white p-6 shadow-2xl">
      <p class="text-sm font-black uppercase tracking-[0.2em] text-red-600">Confirmar accion</p>
      <h2 id="confirm-modal-title" class="mt-3 text-2xl font-black text-slate-900">Antes de continuar</h2>
      <p id="confirm-modal-message" class="mt-3 text-sm leading-6 text-slate-600">
        Esta accion no se puede deshacer.
      </p>

      <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <button
          type="button"
          id="confirm-modal-cancel"
          class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-100"
        >
          Cancelar
        </button>
        <button
          type="button"
          id="confirm-modal-submit"
          class="inline-flex min-h-11 items-center justify-center rounded-xl bg-red-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-red-700"
        >
          Confirmar
        </button>
      </div>
    </div>
  </div>
</div>
