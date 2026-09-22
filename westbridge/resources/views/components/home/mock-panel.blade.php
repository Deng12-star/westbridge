@props(['type' => 'dashboard', 'label' => null, 'compact' => false])

{{--
    Stylised product panels drawn in HTML and CSS, standing in for screenshots.
    No stock imagery, nothing to license, sharp at any size, and a few hundred
    bytes each. Swap for real screenshots of delivered systems when you have
    them.
--}}
<div {{ $attributes->merge(['class' => 'wb-mock relative mx-auto w-full'.($compact ? '' : ' max-w-xl')]) }}>
    <div class="absolute -inset-4 -z-10 rounded-sm bg-lime-500/10 blur-2xl" aria-hidden="true"></div>

    <div class="overflow-hidden rounded-sm border border-white/10 bg-navy-900 shadow-[0_30px_60px_-20px_rgba(0,0,0,0.6)]">
        {{-- window chrome --}}
        <div class="flex items-center gap-1.5 border-b border-white/10 bg-navy-950/60 px-4 py-3">
            <span class="h-2.5 w-2.5 rounded-full bg-white/20"></span>
            <span class="h-2.5 w-2.5 rounded-full bg-white/20"></span>
            <span class="h-2.5 w-2.5 rounded-full bg-lime-500/80"></span>
            @if ($label)
                <span class="ml-3 truncate rounded-full bg-white/[0.06] px-3 py-0.5 font-mono text-[10px] text-navy-300">{{ $label }}</span>
            @else
                <span class="ml-3 h-2 w-32 rounded-full bg-white/10"></span>
            @endif
        </div>

        <div class="p-5">
            @switch($type)
                @case('dashboard')
                    <div class="grid grid-cols-3 gap-3">
                        @foreach ([['Sales', '72%'], ['Stock', '48%'], ['Orders', '86%']] as [$label, $w])
                            <div class="rounded-sm border border-white/10 bg-white/[0.03] p-3">
                                <p class="font-mono text-[10px] uppercase tracking-wider text-navy-300">{{ $label }}</p>
                                <div class="mt-2 h-4 w-12 rounded-xs bg-white/80"></div>
                                <div class="mt-3 h-1 rounded-full bg-white/10"><div class="wb-mock-bar h-1 rounded-full bg-lime-500" style="--w: {{ $w }}"></div></div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3 flex h-36 items-end gap-2 rounded-sm border border-white/10 bg-white/[0.03] p-4">
                        @foreach ([40, 62, 48, 75, 58, 88, 70, 95, 64, 82] as $h)
                            <div class="wb-mock-col flex-1 rounded-t-xs {{ $loop->last ? 'bg-lime-500' : 'bg-navy-500' }}" style="--h: {{ $h }}%; --i: {{ $loop->index }}"></div>
                        @endforeach
                    </div>
                    @break

                @case('school')
                    <div class="flex items-center justify-between">
                        <div class="h-3 w-28 rounded-full bg-white/70"></div>
                        <div class="h-6 w-20 rounded-xs bg-lime-500"></div>
                    </div>
                    <div class="mt-4 grid gap-2">
                        @foreach ([['Paid', 'bg-lime-500'], ['Paid', 'bg-lime-500'], ['Partial', 'bg-[#E0A44B]'], ['Paid', 'bg-lime-500'], ['Due', 'bg-white/30']] as [$status, $color])
                            <div style="--i: {{ $loop->index }}" class="wb-mock-row flex items-center gap-3 rounded-sm border border-white/10 bg-white/[0.03] px-3 py-2.5">
                                <span class="h-7 w-7 flex-shrink-0 rounded-full bg-navy-600"></span>
                                <span class="h-2 flex-1 rounded-full bg-white/25"></span>
                                <span class="h-2 w-10 rounded-full bg-white/15"></span>
                                <span class="rounded-full px-2 py-0.5 font-mono text-[10px] text-navy-900 {{ $color }}">{{ $status }}</span>
                            </div>
                        @endforeach
                    </div>
                    @break

                @case('network')
                    <svg viewBox="0 0 400 230" class="h-auto w-full max-w-full" aria-hidden="true">
                        <g stroke="#3E5180" stroke-width="1.5" fill="none">
                            <path class="wb-mock-link" pathLength="1" d="M200 40 L90 120 M200 40 L200 120 M200 40 L310 120 M90 120 L50 195 M90 120 L130 195 M200 120 L200 195 M310 120 L270 195 M310 120 L350 195"/>
                        </g>
                        <g fill="#16223E" stroke="#6B7CA0" stroke-width="1.5">
                            <circle cx="90" cy="120" r="13"/><circle cx="200" cy="120" r="13"/><circle cx="310" cy="120" r="13"/>
                            <circle cx="50" cy="195" r="9"/><circle cx="130" cy="195" r="9"/><circle cx="200" cy="195" r="9"/><circle cx="270" cy="195" r="9"/><circle cx="350" cy="195" r="9"/>
                        </g>
                        <circle cx="200" cy="40" r="18" fill="#89C726"/>
                        <circle class="wb-mock-pulse" cx="200" cy="40" r="18" fill="none" stroke="#89C726" stroke-width="2"/>
                        <path d="M190 40 l6 6 l10 -12" stroke="#101829" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    @break

                @case('store')
                    <div class="grid grid-cols-3 gap-3">
                        @foreach (range(1, 6) as $n)
                            <div style="--i: {{ $loop->index }}" class="wb-mock-row rounded-sm border border-white/10 bg-white/[0.03] p-3">
                                <div class="flex aspect-square w-full max-w-full items-center justify-center rounded-xs bg-navy-700">
                                    <x-icons.wb :name="['device','device','spark','network','device','spark'][$n-1]" class="h-6 w-6 text-navy-300" />
                                </div>
                                <div class="mt-2.5 h-2 w-full rounded-full bg-white/25"></div>
                                <div class="mt-1.5 h-2 w-1/2 rounded-full bg-lime-500/80"></div>
                            </div>
                        @endforeach
                    </div>
                    @break
                @case('payroll')
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-mono text-[10px] uppercase tracking-wider text-navy-300">Payroll run</p>
                            <div class="mt-1.5 h-3 w-32 rounded-full bg-white/70"></div>
                        </div>
                        <span class="rounded-xs bg-lime-500 px-2.5 py-1 font-mono text-[10px] font-semibold text-navy-900">Payment file</span>
                    </div>
                    <div class="mt-4 grid grid-cols-3 gap-2">
                        @foreach ([['Gross', '82%'], ['Deductions', '34%'], ['Net', '66%']] as [$l, $w])
                            <div class="rounded-sm border border-white/10 bg-white/[0.03] p-2.5">
                                <p class="font-mono text-[9px] uppercase tracking-wider text-navy-300">{{ $l }}</p>
                                <div class="mt-2 h-1 rounded-full bg-white/10"><div class="wb-mock-bar h-1 rounded-full bg-lime-500" style="--w: {{ $w }}"></div></div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3 grid gap-1.5">
                        @foreach (range(1, 4) as $n)
                            <div style="--i: {{ $loop->index }}" class="wb-mock-row flex items-center gap-3 rounded-sm border border-white/10 bg-white/[0.03] px-3 py-2">
                                <span class="h-6 w-6 flex-shrink-0 rounded-full bg-navy-600"></span>
                                <span class="h-2 flex-1 rounded-full bg-white/25"></span>
                                <span class="h-2 w-14 rounded-full bg-white/15"></span>
                                <span class="rounded-full bg-white/10 px-2 py-0.5 font-mono text-[9px] text-navy-200">Payslip</span>
                            </div>
                        @endforeach
                    </div>
                    @break

                @case('loans')
                    <div class="grid grid-cols-5 gap-3">
                        <div class="col-span-3 rounded-sm border border-white/10 bg-white/[0.03] p-3">
                            <p class="font-mono text-[10px] uppercase tracking-wider text-navy-300">Application</p>
                            <div class="mt-3 grid gap-2">
                                @foreach (['Submitted', 'Under review', 'Approved'] as $step)
                                    <div style="--i: {{ $loop->index }}" class="wb-mock-row flex items-center gap-2">
                                        <span @class(['flex h-4 w-4 flex-shrink-0 items-center justify-center rounded-full', 'bg-lime-500' => ! $loop->last, 'border border-lime-500' => $loop->last])></span>
                                        <span class="font-mono text-[10px] text-navy-200">{{ $step }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-span-2 rounded-sm border border-white/10 bg-white/[0.03] p-3">
                            <p class="font-mono text-[10px] uppercase tracking-wider text-navy-300">Repayment</p>
                            <svg viewBox="0 0 80 80" class="mx-auto mt-2 h-16 w-16" aria-hidden="true">
                                <circle cx="40" cy="40" r="30" fill="none" stroke="rgba(255,255,255,.1)" stroke-width="8"/>
                                <path class="wb-mock-link" pathLength="1" d="M40 10 A30 30 0 1 1 19.46 61.87" fill="none" stroke="#89C726" stroke-width="8" stroke-linecap="round"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3 flex h-20 items-end gap-1.5 rounded-sm border border-white/10 bg-white/[0.03] p-3">
                        @foreach ([90, 82, 74, 66, 58, 50, 42, 34, 26, 18, 10] as $h)
                            <div class="wb-mock-col flex-1 rounded-t-xs {{ $loop->first ? 'bg-lime-500' : 'bg-navy-500' }}" style="--h: {{ $h }}%; --i: {{ $loop->index }}"></div>
                        @endforeach
                    </div>
                    @break

                @case('shop')
                    <div class="flex items-center gap-2">
                        <span class="h-6 flex-1 rounded-xs bg-white/[0.06]"></span>
                        <span class="rounded-xs bg-white/10 px-2 py-1 font-mono text-[9px] text-navy-200">USD</span>
                        <span class="rounded-xs bg-lime-500 px-2 py-1 font-mono text-[9px] font-semibold text-navy-900">SSP</span>
                    </div>
                    <div class="mt-3 grid grid-cols-4 gap-2">
                        @foreach (range(1, 8) as $n)
                            <div style="--i: {{ $loop->index }}" class="wb-mock-row rounded-sm border border-white/10 bg-white/[0.03] p-2">
                                <div class="relative aspect-square w-full rounded-xs bg-navy-700">
                                    @if (in_array($n, [1, 4, 6]))
                                        <span class="absolute left-1 top-1 rounded-xs bg-lime-500 px-1 font-mono text-[8px] font-semibold text-navy-900">-29%</span>
                                    @endif
                                </div>
                                <div class="mt-2 h-1.5 w-full rounded-full bg-white/25"></div>
                                <div class="mt-1 h-1.5 w-1/2 rounded-full bg-lime-500/80"></div>
                            </div>
                        @endforeach
                    </div>
                    @break
                @case('cctv')
                    <div class="flex items-center justify-between">
                        <p class="font-mono text-[10px] uppercase tracking-wider text-navy-300">Live view · 4 cameras</p>
                        <span class="flex items-center gap-1.5 font-mono text-[10px] font-semibold text-[#ff5a5a]"><span class="wb-rec-dot h-2 w-2 rounded-full bg-[#ff5a5a]"></span>REC</span>
                    </div>
                    <div class="mt-3 grid grid-cols-2 gap-2">
                        @foreach (['Gate', 'Reception', 'Parking', 'Store room'] as $cam)
                            <div style="--i: {{ $loop->index }}" class="wb-mock-row relative aspect-video overflow-hidden rounded-xs border border-white/10 bg-[linear-gradient(135deg,#16223E,#0A1019)]">
                                <svg viewBox="0 0 160 90" class="absolute inset-0 h-full w-full" preserveAspectRatio="none" aria-hidden="true">
                                    @if ($loop->index === 0)
                                        <path d="M0 70 L60 55 L160 62 L160 90 L0 90Z" fill="#1f2c4c"/><rect x="95" y="30" width="6" height="34" fill="#3E5180"/><rect x="110" y="30" width="6" height="34" fill="#3E5180"/><rect x="125" y="30" width="6" height="34" fill="#3E5180"/>
                                    @elseif ($loop->index === 1)
                                        <rect x="20" y="48" width="70" height="18" fill="#26355c"/><circle cx="120" cy="40" r="8" fill="#3E5180"/><rect x="112" y="50" width="16" height="22" rx="4" fill="#3E5180"/>
                                    @elseif ($loop->index === 2)
                                        <path d="M0 60 L160 50 L160 90 L0 90Z" fill="#1f2c4c"/><rect x="30" y="52" width="36" height="14" rx="4" fill="#3E5180"/><rect x="90" y="48" width="36" height="14" rx="4" fill="#89C726" opacity=".55"/>
                                    @else
                                        <rect x="10" y="20" width="40" height="55" fill="#26355c"/><rect x="60" y="30" width="40" height="45" fill="#26355c"/><rect x="110" y="25" width="40" height="50" fill="#26355c"/>
                                    @endif
                                </svg>
                                <span class="absolute left-1.5 top-1.5 rounded-[2px] bg-black/50 px-1 font-mono text-[8px] text-white">CAM {{ $loop->iteration }} · {{ $cam }}</span>
                                <span class="wb-scan pointer-events-none absolute inset-x-0 h-6 bg-gradient-to-b from-transparent via-lime-400/10 to-transparent" aria-hidden="true"></span>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3 flex items-center gap-2 rounded-sm border border-white/10 bg-white/[0.03] px-3 py-2">
                        <x-icons.wb name="intercom" class="h-4 w-4 text-lime-400" />
                        <span class="font-mono text-[10px] text-navy-200">Gate intercom - visitor at the gate</span>
                        <span class="ml-auto rounded-xs bg-lime-500 px-2 py-0.5 font-mono text-[9px] font-semibold text-navy-900">Open</span>
                    </div>
                    @break
            @endswitch
        </div>
    </div>
</div>
