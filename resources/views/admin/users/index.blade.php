@extends('layouts.admin')

@section('content')
<div class="container">
    <h2>All Users</h2>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Sensor ID</th>
                <th>Expires At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @foreach($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->license->sensor_id ?? 'N/A' }}</td>
                <td>{{ $user->license->expires_at ?? 'N/A' }}</td>
                <td>
                    <form action="{{ route('admin.users.updateLicense', $user->id) }}" method="POST">
                        @csrf
                        @method('POST')
                        <input type="text" name="sensor_id" placeholder="Sensor ID" value="{{ $user->license->sensor_id ?? '' }}" required>
                        <input type="date" name="expires_at" value="{{ optional($user->license)->expires_at ? optional($user->license)->expires_at->format('Y-m-d') : '' }}" required>
                        <button type="submit" class="btn btn-sm btn-primary">Save</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
