@extends('layouts.admin')

@php
  $adminSection = 'users';
@endphp

@push('styles')
  <style>
    .users-directory {
      container: users-directory / inline-size;
      --users-green: #17683e;
      --users-ink: #183b29;
      --users-muted: #68776c;
      --users-border: #e6ece6;
      padding: clamp(1rem, 2vw, 1.6rem) !important;
      border: 1px solid var(--users-border) !important;
      border-radius: 20px !important;
      background: linear-gradient(150deg, #fff, #fbfdfb) !important;
      box-shadow: 0 16px 40px rgba(20, 52, 34, .07) !important;
    }

    .users-directory__header {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 1.5rem;
      margin-bottom: 1.35rem;
    }

    .users-directory__eyebrow {
      display: inline-flex;
      align-items: center;
      gap: .45rem;
      color: #62816c;
      font-size: .7rem;
      font-weight: 800;
      letter-spacing: .1em;
      text-transform: uppercase;
    }

    .users-directory__header h1 {
      margin: .35rem 0 .4rem;
      color: var(--users-ink);
      font-size: clamp(1.6rem, 3vw, 2.05rem);
      letter-spacing: -.035em;
    }

    .users-directory__header p {
      color: var(--users-muted);
      font-size: .88rem;
    }

    .users-directory__create {
      display: inline-flex;
      flex: 0 0 auto;
      align-items: center;
      justify-content: center;
      gap: .55rem;
      min-height: 43px !important;
      padding: .7rem .95rem !important;
      border-radius: 11px !important;
      font-size: .79rem !important;
      font-weight: 750 !important;
    }

    .users-directory__stats {
      display: grid;
      grid-template-columns: repeat(5, minmax(0, 1fr));
      gap: .75rem;
      margin-bottom: 1.15rem;
    }

    .users-directory__stat {
      display: flex;
      align-items: center;
      gap: .8rem;
      min-width: 0;
      padding: .9rem 1rem;
      border: 1px solid var(--users-border);
      border-radius: 13px;
      background: #fff;
    }

    .users-directory__stat-icon {
      display: grid;
      flex: 0 0 auto;
      place-items: center;
      width: 39px;
      height: 39px;
      border-radius: 11px;
      background: #eff6f0;
      color: var(--users-green);
    }

    .users-directory__stat:nth-child(2) .users-directory__stat-icon {
      background: #f8f2e6;
      color: #91722f;
    }

    .users-directory__stat:nth-child(3) .users-directory__stat-icon {
      background: #edf3fa;
      color: #486b91;
    }

    .users-directory__stat:nth-child(4) .users-directory__stat-icon {
      background: #f2eef8;
      color: #766095;
    }

    .users-directory__stat-copy span,
    .users-directory__stat-copy strong {
      display: block;
    }

    .users-directory__stat-copy span {
      color: var(--users-muted);
      font-size: .7rem;
      font-weight: 650;
    }

    .users-directory__stat-copy strong {
      margin-top: .05rem;
      color: var(--users-ink);
      font-family: var(--font-serif);
      font-size: 1.3rem;
      line-height: 1.2;
    }

    .users-directory__toolbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: .85rem;
      margin-bottom: .9rem;
      padding: .8rem;
      border: 1px solid var(--users-border);
      border-radius: 13px;
      background: #f8faf8;
    }

    .users-directory__filters {
      display: flex;
      flex-wrap: wrap;
      gap: .4rem;
    }

    .users-directory__filter {
      display: inline-flex;
      align-items: center;
      gap: .45rem;
      min-height: 35px;
      padding: .42rem .68rem;
      border: 1px solid transparent;
      border-radius: 9px;
      color: #627064;
      font-size: .74rem;
      font-weight: 750;
      transition: background .18s ease, color .18s ease, border-color .18s ease;
    }

    .users-directory__filter:hover {
      border-color: #dce6dc;
      background: #fff;
      color: var(--users-green);
    }

    .users-directory__filter[aria-current="page"] {
      border-color: #d5e6d7;
      background: #eaf4ec;
      color: #17683e;
    }

    .users-directory__filter-count {
      min-width: 1.3rem;
      padding: .08rem .3rem;
      border-radius: 999px;
      background: rgba(29, 85, 51, .08);
      text-align: center;
      font-size: .67rem;
      font-weight: 800;
    }

    .users-directory__search {
      display: flex;
      flex: 0 1 330px;
      align-items: center;
      gap: .45rem;
      min-width: 230px;
    }

    .users-directory__search label {
      position: absolute;
      width: 1px;
      height: 1px;
      overflow: hidden;
      clip: rect(0, 0, 0, 0);
      white-space: nowrap;
    }

    .users-directory__search input {
      width: 100%;
      min-width: 0;
      height: 38px;
      padding: .55rem .7rem;
      border: 1px solid #dfe7df;
      border-radius: 9px;
      background: #fff;
      color: #294333;
      font: inherit;
      font-size: .76rem;
    }

    .users-directory__search input:focus {
      outline: 3px solid rgba(23, 104, 62, .14);
      border-color: #8daf93;
    }

    .users-directory__search button {
      display: inline-flex;
      flex: 0 0 auto;
      align-items: center;
      justify-content: center;
      gap: .4rem;
      min-height: 38px;
      padding: .55rem .75rem;
      border-radius: 9px;
      background: #17683e;
      color: #fff;
      font-size: .74rem;
      font-weight: 750;
    }

    .users-directory__table-wrap {
      padding: 0 !important;
      overflow: auto;
      border: 1px solid var(--users-border) !important;
      border-radius: 14px !important;
      background: #fff !important;
      box-shadow: none !important;
      scrollbar-width: thin;
    }

    .users-directory__table {
      width: 100% !important;
      min-width: 740px;
      border-collapse: separate !important;
      border-spacing: 0 !important;
      table-layout: auto;
    }

    .admin-users .users-directory__table thead {
      display: table-header-group !important;
      padding: 0 !important;
      background: #f7faf7;
      color: #68776c;
      font-size: .67rem;
      letter-spacing: .07em;
      text-transform: uppercase;
    }

    .users-directory__table thead th {
      padding: .8rem .9rem !important;
      border-bottom: 1px solid var(--users-border) !important;
      text-align: left;
      white-space: nowrap;
    }

    .admin-users .users-directory__table tbody {
      display: table-row-group !important;
    }

    .admin-users .users-directory__table tbody tr {
      display: table-row !important;
      margin: 0 !important;
      border-radius: 0 !important;
      background: #fff;
      box-shadow: none !important;
      transition: background .15s ease;
    }

    .users-directory__table tbody tr:hover {
      background: #f9fbf9 !important;
      transform: none !important;
    }

    .admin-users .users-directory__table tbody td {
      padding: .72rem .9rem !important;
      border-bottom: 1px solid #edf1ed !important;
      color: #4d5b50;
      vertical-align: middle !important;
    }

    .users-directory__table tbody tr:last-child td {
      border-bottom: 0 !important;
    }

    .users-directory__identity {
      display: flex;
      align-items: center;
      gap: .7rem;
      min-width: 175px;
    }

    .users-directory__avatar {
      display: grid;
      flex: 0 0 auto;
      place-items: center;
      width: 35px;
      height: 35px;
      border: 1px solid #dcebdd;
      border-radius: 11px;
      background: linear-gradient(145deg, #edf6ee, #f7f3e9);
      color: #296344;
      font-family: var(--font-serif);
      font-size: .95rem;
      font-weight: 800;
    }

    .users-directory__name {
      color: #263c2d;
      font-size: .78rem;
      font-weight: 750;
    }

    .users-directory__email {
      color: #57665b !important;
      font-size: .75rem !important;
      overflow-wrap: anywhere;
    }

    .users-directory__role {
      display: inline-flex;
      align-items: center;
      gap: .35rem;
      padding: .32rem .55rem;
      border: 1px solid #e3ebe3;
      border-radius: 999px;
      background: #f7f9f7;
      color: #536357;
      font-size: .66rem;
      font-weight: 750;
      white-space: nowrap;
    }

    .users-directory__role--admin {
      border-color: #eadfbe;
      background: #fbf6e9;
      color: #876b2a;
    }

    .users-directory__role--docente {
      border-color: #d8e8da;
      background: #eff7f0;
      color: #286140;
    }

    .users-directory__role--director {
      border-color: #d9dff0;
      background: #f0f2fa;
      color: #465a8a;
    }

    .users-directory__role--alumno {
      border-color: #dce5ef;
      background: #f1f5f9;
      color: #456785;
    }

    .users-directory__date {
      color: #68776c;
      font-size: .74rem;
      white-space: nowrap;
    }

    .users-directory__actions {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      gap: .4rem;
      min-width: 160px;
    }

    .users-directory .users-directory__actions .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: .35rem;
      min-height: 32px !important;
      padding: .4rem .58rem !important;
      border: 1px solid #dde7de;
      border-radius: 8px !important;
      background: #fff;
      color: #42614a;
      font-size: .69rem !important;
      font-weight: 750;
      box-shadow: none;
      transition: background .18s ease, border-color .18s ease, color .18s ease;
    }

    .users-directory .users-directory__actions .btn:hover {
      transform: none;
      border-color: #b6cfb9;
      background: #eff7f0;
      color: #17683e;
      box-shadow: none;
    }

    .users-directory .users-directory__actions .btn--danger {
      border-color: #eedbda;
      color: #a24a42;
    }

    .users-directory .users-directory__actions .btn--danger:hover {
      border-color: #ecc3bf;
      background: #fff4f2;
      color: #922e27;
    }

    .users-directory__empty {
      padding: 2.5rem 1rem;
      text-align: center;
    }

    .users-directory__empty-icon {
      display: grid;
      place-items: center;
      width: 46px;
      height: 46px;
      margin: 0 auto .75rem;
      border-radius: 14px;
      background: #eff6f0;
      color: #37704a;
      font-size: 1.1rem;
    }

    .users-directory__empty strong {
      display: block;
      color: var(--users-ink);
      font-size: .9rem;
    }

    .users-directory__empty p {
      margin: .25rem 0 .75rem;
      color: var(--users-muted);
      font-size: .78rem;
    }

    .users-directory__empty a {
      color: var(--users-green);
      font-size: .76rem;
      font-weight: 750;
      text-decoration: underline;
      text-underline-offset: 3px;
    }

    .users-directory__results {
      margin-top: .7rem;
      color: var(--users-muted);
      font-size: .72rem;
    }

    .users-directory__alert {
      margin-bottom: .8rem;
      padding: .75rem .9rem;
      border: 1px solid #cce6d1;
      border-radius: 10px;
      background: #f0f8f1;
      color: #28633a;
      font-size: .8rem;
    }

    .users-directory__alert--error {
      border-color: #f0d0cc;
      background: #fff5f3;
      color: #9c3d32;
    }

    .users-directory .admin-pagination {
      margin-top: .75rem;
    }

    @media (max-width: 1000px) {
      .users-directory__stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .users-directory__toolbar {
        align-items: flex-start;
        flex-direction: column;
      }

      .users-directory__search {
        flex-basis: auto;
        width: 100%;
      }
    }

    @media (max-width: 620px) {
      .users-directory__header {
        align-items: stretch;
        flex-direction: column;
        gap: .8rem;
      }

      .users-directory__create {
        width: 100%;
      }

      .users-directory__stats {
        gap: .55rem;
      }

      .users-directory__stat {
        gap: .55rem;
        padding: .7rem .65rem;
      }

      .users-directory__stat-icon {
        width: 33px;
        height: 33px;
      }

      .users-directory__stat-copy span {
        font-size: .65rem;
      }

      .users-directory__filters {
        width: 100%;
      }

      .users-directory__filter {
        flex: 1 1 calc(50% - .4rem);
        justify-content: center;
      }

      .users-directory__search {
        min-width: 0;
      }
    }

    @media (max-width: 720px) {
      .admin-users .users-directory__table {
        display: block;
        min-width: 0 !important;
      }

      .admin-users .users-directory__table thead {
        display: none !important;
      }

      .admin-users .users-directory__table tbody {
        display: grid !important;
        gap: .65rem;
        padding: .65rem;
      }

      .admin-users .users-directory__table tbody tr {
        display: grid !important;
        grid-template-columns: 1fr 1fr;
        gap: .5rem .75rem;
        padding: .8rem;
        border: 1px solid var(--users-border);
        border-radius: 12px !important;
        background: #fff;
      }

      .admin-users .users-directory__table tbody td {
        display: grid !important;
        gap: .2rem;
        min-width: 0;
        padding: 0 !important;
        border: 0 !important;
      }

      .admin-users .users-directory__table tbody td::before {
        content: attr(data-label);
        color: #7a887d;
        font-size: .61rem;
        font-weight: 750;
        letter-spacing: .06em;
        text-transform: uppercase;
      }

      .admin-users .users-directory__table tbody td:first-child {
        grid-column: 1 / -1;
      }

      .admin-users .users-directory__table tbody td.users-directory__actions {
        grid-column: 1 / -1;
        justify-content: flex-start;
        min-width: 0;
        padding-top: .35rem !important;
        border-top: 1px solid #edf1ed !important;
      }

      .users-directory__empty-cell::before {
        display: none;
      }
    }

    @container users-directory (max-width: 720px) {
      .admin-users .users-directory__table {
        display: block;
        min-width: 0 !important;
      }

      .admin-users .users-directory__table thead {
        display: none !important;
      }

      .admin-users .users-directory__table tbody {
        display: grid !important;
        gap: .65rem;
        padding: .65rem;
      }

      .admin-users .users-directory__table tbody tr {
        display: grid !important;
        grid-template-columns: 1fr 1fr;
        gap: .5rem .75rem;
        padding: .8rem;
        border: 1px solid var(--users-border);
        border-radius: 12px !important;
        background: #fff;
      }

      .admin-users .users-directory__table tbody td {
        display: grid !important;
        gap: .2rem;
        min-width: 0;
        padding: 0 !important;
        border: 0 !important;
      }

      .admin-users .users-directory__table tbody td::before {
        content: attr(data-label);
        color: #7a887d;
        font-size: .61rem;
        font-weight: 750;
        letter-spacing: .06em;
        text-transform: uppercase;
      }

      .admin-users .users-directory__table tbody td:first-child {
        grid-column: 1 / -1;
      }

      .admin-users .users-directory__table tbody td.users-directory__actions {
        grid-column: 1 / -1;
        justify-content: flex-start;
        min-width: 0;
        padding-top: .35rem !important;
        border-top: 1px solid #edf1ed !important;
      }

      .users-directory__empty-cell::before {
        display: none;
      }
    }
  </style>
@endpush

@section('admin_content')
  <section class="admin-card admin-users users-directory" aria-labelledby="usersDirectoryTitle">
    <header class="users-directory__header">
      <div>
        <span class="users-directory__eyebrow"><i class="fas fa-users" aria-hidden="true"></i> Directorio institucional</span>
        <h1 id="usersDirectoryTitle">Gestión de usuarios</h1>
        <p>Administra las cuentas y los permisos de acceso de la comunidad educativa.</p>
      </div>
      <a href="{{ route('admin.users.create') }}" class="btn btn-primary users-directory__create">
        <i class="fas fa-user-plus" aria-hidden="true"></i> Crear usuario
      </a>
    </header>

    @if(session('success'))
      <div class="users-directory__alert" role="status">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="users-directory__alert users-directory__alert--error" role="alert">{{ session('error') }}</div>
    @endif

    <section class="users-directory__stats" aria-label="Resumen de cuentas">
      <article class="users-directory__stat">
        <span class="users-directory__stat-icon"><i class="fas fa-users" aria-hidden="true"></i></span>
        <div class="users-directory__stat-copy"><span>Total de cuentas</span><strong>{{ number_format($stats['total']) }}</strong></div>
      </article>
      <article class="users-directory__stat">
        <span class="users-directory__stat-icon"><i class="fas fa-user-shield" aria-hidden="true"></i></span>
        <div class="users-directory__stat-copy"><span>Administradores</span><strong>{{ number_format($stats['admins']) }}</strong></div>
      </article>
      <article class="users-directory__stat">
        <span class="users-directory__stat-icon"><i class="fas fa-user-tie" aria-hidden="true"></i></span>
        <div class="users-directory__stat-copy"><span>Directores</span><strong>{{ number_format($stats['directors']) }}</strong></div>
      </article>
      <article class="users-directory__stat">
        <span class="users-directory__stat-icon"><i class="fas fa-chalkboard-teacher" aria-hidden="true"></i></span>
        <div class="users-directory__stat-copy"><span>Docentes</span><strong>{{ number_format($stats['teachers']) }}</strong></div>
      </article>
      <article class="users-directory__stat">
        <span class="users-directory__stat-icon"><i class="fas fa-user-graduate" aria-hidden="true"></i></span>
        <div class="users-directory__stat-copy"><span>Alumnos</span><strong>{{ number_format($stats['students']) }}</strong></div>
      </article>
    </section>

    <div class="users-directory__toolbar">
      @php
        $roleFilters = [
          ['value' => null, 'label' => 'Todos', 'count' => $stats['total']],
          ['value' => 'admin', 'label' => 'Administradores', 'count' => $stats['admins']],
          ['value' => 'director', 'label' => 'Directores', 'count' => $stats['directors']],
          ['value' => 'docente', 'label' => 'Docentes', 'count' => $stats['teachers']],
          ['value' => 'alumno', 'label' => 'Alumnos', 'count' => $stats['students']],
        ];
      @endphp
      <nav class="users-directory__filters" aria-label="Filtrar usuarios por rol">
        @foreach($roleFilters as $filter)
          @php
            $filterQuery = array_filter([
              'role' => $filter['value'],
              'q' => $search,
            ], fn ($value) => $value !== null && $value !== '');
            $isActiveFilter = ($filter['value'] ?? '') === ($role ?? '');
          @endphp
          <a
            class="users-directory__filter"
            href="{{ route('admin.users.index', $filterQuery) }}"
            @if($isActiveFilter) aria-current="page" @endif
          >
            {{ $filter['label'] }}
            <span class="users-directory__filter-count">{{ number_format($filter['count']) }}</span>
          </a>
        @endforeach
      </nav>

      <form class="users-directory__search" method="GET" action="{{ route('admin.users.index') }}" role="search">
        <label for="usersSearch">Buscar por nombre o correo</label>
        @if($role && array_key_exists($role, \App\Models\User::ROLES))
          <input type="hidden" name="role" value="{{ $role }}" />
        @endif
        <input id="usersSearch" type="search" name="q" value="{{ $search }}" placeholder="Nombre o correo electrónico" maxlength="100" />
        <button type="submit"><i class="fas fa-search" aria-hidden="true"></i><span>Buscar</span></button>
      </form>
    </div>

    <div class="admin-table-wrap users-directory__table-wrap">
      <table class="table admin-table users-directory__table">
        <thead>
          <tr>
            <th scope="col">Usuario</th>
            <th scope="col">Correo electrónico</th>
            <th scope="col">Rol</th>
            <th scope="col">Fecha de registro</th>
            <th scope="col"><span class="sr-only">Acciones</span></th>
          </tr>
        </thead>
        <tbody>
          @forelse($users as $userRecord)
            @php
              $roleLabels = \App\Models\User::ROLES;
              $roleLabel = $roleLabels[$userRecord->role] ?? ucfirst($userRecord->role);
            @endphp
            <tr>
              <td data-label="Usuario">
                <div class="users-directory__identity">
                  <span class="users-directory__avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($userRecord->name, 0, 1)) }}</span>
                  <span class="users-directory__name">{{ $userRecord->name }}</span>
                </div>
              </td>
              <td class="users-directory__email" data-label="Correo electrónico">{{ $userRecord->email }}</td>
              <td data-label="Rol">
                <span class="users-directory__role users-directory__role--{{ array_key_exists($userRecord->role, \App\Models\User::ROLES) ? $userRecord->role : 'alumno' }}">
                  <i class="fas {{ $userRecord->role === 'admin' ? 'fa-shield-halved' : ($userRecord->role === 'director' ? 'fa-user-tie' : ($userRecord->role === 'docente' ? 'fa-chalkboard-teacher' : 'fa-user-graduate')) }}" aria-hidden="true"></i>
                  {{ $roleLabel }}
                </span>
              </td>
              <td class="users-directory__date" data-label="Fecha de registro">
                <time datetime="{{ $userRecord->created_at->toDateString() }}">{{ $userRecord->created_at->format('d/m/Y') }}</time>
              </td>
              <td class="admin-actions-cell users-directory__actions" data-label="Acciones">
                <a href="{{ route('admin.users.edit', $userRecord->id) }}" class="btn btn-sm" aria-label="Editar usuario {{ $userRecord->name }}">
                  <i class="fas fa-pen" aria-hidden="true"></i><span>Editar</span>
                </a>
                @if(auth()->id() !== $userRecord->id)
                  <form action="{{ route('admin.users.destroy', $userRecord->id) }}" method="POST" class="confirm-delete admin-inline-form">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn--danger" type="submit" aria-label="Eliminar usuario {{ $userRecord->name }}">
                      <i class="fas fa-trash-can" aria-hidden="true"></i><span>Eliminar</span>
                    </button>
                  </form>
                @else
                  <span class="users-directory__role"><i class="fas fa-lock" aria-hidden="true"></i> Tu cuenta</span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td class="users-directory__empty-cell" colspan="5">
                <div class="users-directory__empty">
                  <span class="users-directory__empty-icon"><i class="fas fa-user-magnifying-glass" aria-hidden="true"></i></span>
                  <strong>No encontramos usuarios</strong>
                  <p>Prueba con otro nombre o correo, o cambia el filtro de rol.</p>
                  @if($search !== '' || $role)
                    <a href="{{ route('admin.users.index') }}">Limpiar filtros</a>
                  @endif
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($users->total() > 0)
      <p class="users-directory__results" aria-live="polite">
        Mostrando {{ $users->firstItem() }}–{{ $users->lastItem() }} de {{ number_format($users->total()) }} usuarios
      </p>
    @endif
    <div class="admin-pagination">{{ $users->links() }}</div>
  </section>
@endsection
