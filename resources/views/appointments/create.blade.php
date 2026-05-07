@extends('layouts.app')

@section('title', 'Reservar Cita - ' . $barbershop->name)

@section('content')
<div class="page-shell max-w-4xl">
  <div class="page-heading">
    <div>
      <h1 class="page-title">Reservar Cita</h1>
      <p class="page-subtitle">{{ $barbershop->name }} · {{ $service->name }}</p>
    </div>
  </div>

  <div
    class="eb-panel booking-calendar-panel overflow-hidden p-8"
    x-data="{
      visibleMonth: @js($initialVisibleMonthIndex),
      selectedDay: @js($initialSelectedDayIndex),
      selectedDatetime: @js($preselectedDatetime ?? ''),
      selectDay(monthIndex, dayIndex, isoDate) {
        this.visibleMonth = monthIndex;
        this.selectedDay = dayIndex;

        if (!this.selectedDatetime || !this.selectedDatetime.startsWith(isoDate)) {
          this.selectedDatetime = '';
        }

        this.$nextTick(() => {
          this.$refs.bookingSlots?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
      }
    }"
  >
    <form action="{{ route('appointments.confirm', $barbershop) }}" method="GET">
      <input type="hidden" name="barbershop_id" value="{{ $barbershop->id }}">
      <input type="hidden" name="service_id" value="{{ $service->id }}">

      <div class="booking-calendar-head mb-8">
        <div>
          <p class="booking-calendar-kicker">Seleccionar fecha y hora</p>
          <h2 class="text-xl font-black text-gray-900">Mes actual y siguiente</h2>
        </div>
        <p class="booking-calendar-note">Elige un día para ver los horarios disponibles.</p>
      </div>

      <div class="mb-6">
        @error('datetime') <p class="mb-4 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror

        <div class="booking-months-stack">
          @foreach($calendarMonths as $month)
          <div class="booking-month-shell" x-cloak x-show="visibleMonth === {{ $loop->index }}">
            <div class="booking-month-title">{{ $month['label'] }}
              <button
              type="button"
              class="booking-month-nav-button"
              @click="visibleMonth = Math.max(0, visibleMonth - 1)"
              :disabled="visibleMonth === 0"
            >
              <
            </button>
            <button
              type="button"
              class="booking-month-nav-button"
              @click="visibleMonth = Math.min({{ count($calendarMonths) - 1 }}, visibleMonth + 1)"
              :disabled="visibleMonth === {{ count($calendarMonths) - 1 }}"
            >
              >
            </button>
            </div>
            <div class="booking-month-weekdays">
              <span>Lun.</span>
              <span>Mar.</span>
              <span>Mie.</span>
              <span>Jue.</span>
              <span>Vie.</span>
              <span>Sab.</span>
              <span>Dom.</span>
            </div>

            <div class="booking-month-grid">
              @foreach($month['days'] as $day)
                @if($day === null)
                  <div class="booking-day-spacer" aria-hidden="true"></div>
                @else
                  @php($isBookableDay = $day['available_slot_count'] > 0)
                  <button
                    type="button"
                    class="booking-day-button {{ $isBookableDay ? '' : 'is-unavailable' }}"
                    data-date="{{ $day['iso_date'] }}"
                    aria-disabled="{{ $isBookableDay ? 'false' : 'true' }}"
                    :class="{ 'is-selected': selectedDay === {{ $day['index'] }}, 'is-empty': {{ $isBookableDay ? 'false' : 'true' }}, 'is-past': {{ $day['is_past'] ? 'true' : 'false' }} }"
                    @click="selectDay({{ $loop->parent->index }}, {{ $day['index'] }}, '{{ $day['iso_date'] }}')"
                    @disabled(!$isBookableDay)
                  >
                    <span class="booking-day-week">{{ $day['weekday_label'] }}</span>
                    <span class="booking-day-number">{{ $day['day_number'] }}</span>
                    <span class="booking-day-pill">
                      @if($day['is_past'])
                        Pasado
                      @elseif($day['available_slot_count'] === 0)
                        Sin citas
                      @else
                        {{ $day['available_slot_count'] }} huecos disponibles
                      @endif
                    </span>
                  </button>
                @endif
              @endforeach
            </div>
          </div>
          @endforeach
        </div>
      </div>

      <div class="booking-slots-panel" x-ref="bookingSlots">
        @foreach($days as $day)
          <section x-cloak x-show="selectedDay === {{ $day['index'] }}" class="booking-slots-day">
            <div class="booking-slots-head">
              <div>
                <p class="booking-calendar-kicker">Día seleccionado</p>
                <h3 class="text-lg font-black text-violet-950">{{ $day['date']->format('l, d M Y') }}</h3>
              </div>
              <p class="booking-slots-meta">
                @if($day['is_past'])
                  Día no reservable
                @elseif($day['available_slot_count'] > 0)
                  {{ $day['available_slot_count'] }} horarios disponibles
                @else
                  Sin disponibilidad
                @endif
              </p>
            </div>

            @if(!empty($day['slots']))
              <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4">
                @foreach($day['slots'] as $slot)
                  <label class="flex items-center">
                    <input
                      type="radio"
                      name="datetime"
                      value="{{ $day['iso_date'] }} {{ $slot['time'] }}"
                      x-model="selectedDatetime"
                      class="hidden peer"
                      @disabled(!$slot['available'])
                    >
                    <div class="slot-choice transition-colors {{ $slot['available'] ? '' : 'slot-choice-unavailable' }}">
                      {{ $slot['time'] }}
                    </div>
                  </label>
                @endforeach
              </div>
            @elseif($day['is_past'])
              <div class="booking-empty-day">
                <p class="text-sm font-bold text-gray-700">
                  Este día ya ha pasado.
                </p>
                <p class="mt-1 text-sm text-gray-500">
                  Selecciona una fecha de hoy en adelante para continuar con la reserva.
                </p>
              </div>
            @else
              <div class="booking-empty-day">
                <p class="text-sm font-bold text-gray-700">
                  No hay horarios disponibles este día.
                </p>
                <p class="mt-1 text-sm text-gray-500">
                  Se muestran todos los huecos del horario, pero no puedes seleccionar los que ya estén ocupados.
                </p>
              </div>
            @endif
          </section>
        @endforeach

        @unless($hasAvailableSlots)
          <p class="mt-6 text-gray-600">No hay horarios disponibles para este servicio entre este mes y el siguiente.</p>
        @endunless
      </div>

      @if($hasAvailableSlots)
        @auth
          <div class="mt-8 flex justify-end">
            <button type="submit" class="eb-button px-6 py-3 disabled:cursor-not-allowed disabled:opacity-50" :disabled="!selectedDatetime">
              Continuar
            </button>
          </div>
        @else
          <div class="mt-8 rounded-2xl border border-amber-200 bg-amber-50 p-5">
            <p class="text-sm font-black uppercase tracking-wide text-amber-800">Aviso</p>
            <p class="mt-2 text-sm font-medium text-amber-900">
              Para reservar una cita, selecciona una hora y después inicia sesión o regístrate. Guardaremos tu selección para que puedas confirmarla justo después.
            </p>

            <div class="mt-4 flex flex-col gap-3 sm:flex-row">
              <button type="submit" formaction="{{ route('login') }}" class="eb-button px-6 py-3 text-center disabled:cursor-not-allowed disabled:opacity-50" :disabled="!selectedDatetime">
                Iniciar sesión
              </button>
              <button type="submit" formaction="{{ route('register') }}" class="rounded-xl border border-amber-300 px-6 py-3 text-center text-sm font-bold text-amber-900 transition hover:bg-amber-100 disabled:cursor-not-allowed disabled:opacity-50" :disabled="!selectedDatetime">
                Registrarse
              </button>
            </div>
          </div>
        @endauth
      @endif
    </form>
  </div>
</div>
@endsection
