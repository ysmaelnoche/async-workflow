@extends('layouts.app')

@section('title', 'TEST DASHBOARD')

@section('content')
<div class="container">
    <h1>THIS IS THE TEST DASHBOARD VIEW</h1>
    <p>If you see this, the routing is working correctly.</p>
    <p>Variables passed:</p>
    <ul>
        <li>Total Requests: {{ $totalRequests ?? 'Not set' }}</li>
        <li>Pending Requests: {{ $pendingRequests ?? 'Not set' }}</li>
        <li>Department Count: {{ $departmentCount ?? 'Not set' }}</li>
    </ul>
</div>
@endsection
