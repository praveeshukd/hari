@extends('layouts.app')

@section('title', 'Users – ' . config('app.name'))
@section('page_title', 'Users')
@section('hide_footer', '1')
@section('hide_nav_links', '1')
@section('hide_navbar', '1')

@section('content')
<div class="admin-wrapper">
    @include('partials.admin-sidebar', ['active' => 'users'])
    <x-admin-header title="Users" />

    <main class="admin-main">
        <div class="spark-pagehead">
            <h1>Users &amp; Roles</h1>
        </div>

        <section class="sheet-card">
            <div class="dt-table-wrap">
                <table class="qc-datatable display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Joined</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td><span class="badge badge-pending">{{ ucfirst($user->role) }}</span></td>
                                <td>{{ $user->created_at?->format('Y-m-d') }}</td>
                                <td>
                                    <form method="POST" action="{{ route('users.update-role', $user) }}" class="products-toolbar-right">
                                        @csrf
                                        @method('PATCH')
                                        <select name="role" class="f-select" aria-label="Role for {{ $user->name }}">
                                            @foreach(\App\Models\User::ROLES as $role)
                                                <option value="{{ $role }}" @selected($user->role === $role)>{{ ucfirst($role) }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="sheet-btn sheet-btn-primary">Save</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="margin-top:1rem;">{{ $users->links() }}</div>
        </section>
    </main>
</div>
@endsection
