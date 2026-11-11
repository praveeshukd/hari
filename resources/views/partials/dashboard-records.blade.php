@php($tone = $tone ?? 'pass')
@if($rows->isEmpty())
    <div class="mdash-empty">
        <p>{{ $empty }}</p>
        <span>New QC results will show up here.</span>
    </div>
@else
    <ul class="mdash-record-list">
        @foreach($rows as $row)
            <li class="mdash-record-row">
                <span class="mdash-record-info">
                    <span class="mdash-record-avatar mdash-record-avatar--{{ $tone }}" aria-hidden="true">
                        {{ strtoupper(substr($row->sample_name ?: 'S', 0, 1)) }}
                    </span>
                    <span class="mdash-record-copy">
                        <span class="mdash-record-name">{{ $row->sample_name ?: 'Untitled sample' }}</span>
                        <span class="mdash-record-meta">
                            {{ $row->batch_no ?: 'No batch' }}
                            @if($row->date)
                                · {{ $row->date->format('M j, Y') }}
                            @endif
                        </span>
                    </span>
                </span>
                <span class="mdash-record-value mdash-record-value--{{ $tone }}">
                    {{ $row->purity !== null ? number_format($row->purity, 2).'%' : '—' }}
                </span>
            </li>
        @endforeach
    </ul>
@endif
