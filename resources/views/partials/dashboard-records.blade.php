@if($rows->isEmpty())
    <p class="spark-empty">{{ $empty }}</p>
@else
    <ul class="spark-list">
        @foreach($rows as $row)
            <li>
                <div>
                    <div class="spark-list-title">{{ $row->sample_name ?: 'Untitled sample' }}</div>
                    <div class="spark-list-sub">
                        {{ $row->batch_no ?: 'No batch' }}
                        @if($row->date)
                            · {{ $row->date->format('d M Y') }}
                        @endif
                    </div>
                </div>
                <div class="spark-list-value">
                    {{ $row->purity !== null ? number_format($row->purity, 2).'%' : '—' }}
                </div>
            </li>
        @endforeach
    </ul>
@endif
