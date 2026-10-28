@php
  // Accepts $items = [ ['title'=>'','meta'=>'','content'=>''] ]
  $items = $items ?? [
    ['title'=>'¿Cómo matricular a un estudiante?','meta'=>'Proceso','content'=>'Para matricular diríjase a la secretaría con DNI del estudiante y documentos académicos.'],
    ['title'=>'Horarios de atención','meta'=>'Horario','content'=>'Atendemos de lunes a viernes de 08:00 a 16:00.'],
    ['title'=>'Contacto y comunicación','meta'=>'Comunicación','content'=>'Puede contactarnos al correo institucional o vía la plataforma escolar.']
  ];
@endphp

<div class="accordion" role="list">
  @foreach($items as $item)
    <details class="accordion-item" role="listitem" @if($loop->first) open @endif>
      <summary>
        <div class="accordion-label">
          <span class="title">{{ $item['title'] }}</span>
          @if(!empty($item['meta']))<span class="meta">{{ $item['meta'] }}</span>@endif
        </div>
        <span class="accordion-chevron" aria-hidden="true">▾</span>
      </summary>
      <div class="accordion-body">{!! $item['content'] !!}</div>
    </details>
  @endforeach
</div>
