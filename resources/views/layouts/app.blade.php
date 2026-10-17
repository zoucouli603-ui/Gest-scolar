@php($activeSchool = $currentSchool ?? Auth::user()?->school)
@php($appearance = \App\Support\Appearance::settings($activeSchool))
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#1e1b6b">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <link rel="manifest" href="{{ asset('manifest.json') }}">
        <link rel="apple-touch-icon" href="{{ asset('pwa-icon-192.png') }}">
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

        <title>{{ $activeSchool ? $activeSchool->name.' · YELENSCOL' : config('app.name', 'YELENSCOL') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="{{ \App\Support\Appearance::fontsUrl($appearance) }}" rel="stylesheet" />

        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>{!! \App\Support\Appearance::css($appearance) !!}</style>
        @livewireStyles
    </head>
    <body class="font-sans antialiased bg-slate-100 text-slate-800 h-screen overflow-hidden {{ \App\Support\Appearance::bodyClass($appearance) }}">
    <div class="flex h-screen overflow-hidden">

  <!-- Sidebar Latérale à gauche -->
<aside class="w-56 h-screen bg-white border-r border-slate-200 flex flex-col shrink-0">
<div class="min-h-0 flex-1 overflow-y-auto">
<a href="{{ route('home') }}" class="flex items-center border-b border-slate-100 px-5 py-3.5" aria-label="YELENSCOL"><x-brand-logo size="sm" :variant="$appearance['sidebar'] === 'dark' ? 'dark' : 'light'" /></a>
<!-- Nom / Logo de l'école -->
@if($activeSchool)
<div class="p-5 flex items-center gap-3 border-b border-slate-100">
<x-school-logo :school="$activeSchool" class="h-10 w-10 shrink-0 rounded-xl text-lg" />
<div>
    <h2 class="max-w-40 truncate font-bold text-slate-900 text-sm leading-tight">{{ $activeSchool->name }}</h2>
    <span class="text-xs text-slate-400">{{ $activeSchool->currentSchoolYear?->name ?? 'Espace scolaire' }}</span>
</div>
</div>
@else
<div class="px-5 py-4 border-b border-slate-100">
    <span class="inline-flex items-center gap-1.5 rounded-full bg-gold-100 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wider text-gold-700">Super-administration</span>
</div>
@endif

 <!-- Navigation de la Sidebar -->
 <nav class="ys-nav flex flex-col gap-1 p-3 text-sm font-medium">
 @if(Auth::user()->isSuperAdmin())
 <a href="{{ route('owner.dashboard') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 {{ request()->routeIs('owner.*') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5" aria-hidden="true"><path fill-rule="evenodd" d="M4 16.5v-13h-.25a.75.75 0 0 1 0-1.5h12.5a.75.75 0 0 1 0 1.5H16v13h.25a.75.75 0 0 1 0 1.5h-3.5a.75.75 0 0 1-.75-.75v-2.5a.75.75 0 0 0-.75-.75h-2.5a.75.75 0 0 0-.75.75v2.5a.75.75 0 0 1-.75.75h-3.5a.75.75 0 0 1 0-1.5H4Zm3-11a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1Zm.5 3.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1Zm3-3.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1Zm.5 3.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1Z" clip-rule="evenodd"/></svg>
    Écoles utilisant YELENSCOL
 </a>
 @else
 <a href="{{ route('dashboard') }}" data-nav-item style="order: 1{{ Auth::user()->canAccess('dashboard') ? '' : '; display:none' }}"
    class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white font-semibold shadow-sm' :'text-slate-600 hover:bg-slate-100' }}">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
  <path d="M9.293 2.293a1 1 0 0 1 1.414 0l7 7A1 1 0 0 1 17 11h-1v6a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1v-3a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-6H3a1 1 0 0 1-.707-1.707l7-7Z" />
</svg>
Tableau de bord
</a>
<a href="{{ route('school-years.index') }}" data-nav-item style="order: 2{{ Auth::user()->canAccess('school-years') ? '' : '; display:none' }}"
   class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('school-years.*') ? 'bg-blue-600 text-white font-semibold shadow-sm' :'text-slate-600 hover:bg-slate-100' }}">
   <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
  <path d="M4.464 3.162A2 2 0 0 1 6.28 2h7.44a2 2 0 0 1 1.816 1.162l1.154 2.5c.067.145.115.291.145.438A3.508 3.508 0 0 0 16 6H4c-.288 0-.568.035-.835.1.03-.147.078-.293.145-.438l1.154-2.5Z" />
  <path fill-rule="evenodd" d="M2 9.5a2 2 0 0 1 2-2h12a2 2 0 1 1 0 4H4a2 2 0 0 1-2-2Zm13.24 0a.75.75 0 0 1 .75-.75H16a.75.75 0 0 1 .75.75v.01a.75.75 0 0 1-.75.75h-.01a.75.75 0 0 1-.75-.75V9.5Zm-2.25-.75a.75.75 0 0 0-.75.75v.01c0 .414.336.75.75.75H13a.75.75 0 0 0 .75-.75V9.5a.75.75 0 0 0-.75-.75h-.01ZM2 15a2 2 0 0 1 2-2h12a2 2 0 1 1 0 4H4a2 2 0 0 1-2-2Zm13.24 0a.75.75 0 0 1 .75-.75H16a.75.75 0 0 1 .75.75v.01a.75.75 0 0 1-.75.75h-.01a.75.75 0 0 1-.75-.75V15Zm-2.25-.75a.75.75 0 0 0-.75.75v.01c0 .414.336.75.75.75H13a.75.75 0 0 0 .75-.75V15a.75.75 0 0 0-.75-.75h-.01Z" clip-rule="evenodd" />
</svg>
    Année Scolaire
</a>
<a href="{{ route('students.index') }}" data-nav-item style="order: 6{{ Auth::user()->canAccess('students') ? '' : '; display:none' }}"
   class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('students.*') ? 'bg-blue-600 text-white font-semibold shadow-sm' :'text-slate-600 hover:bg-slate-100' }}">
   <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
  <path d="M10 9a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM6 8a2 2 0 1 1-4 0 2 2 0 0 1 4 0ZM1.49 15.326a.78.78 0 0 1-.358-.442 3 3 0 0 1 4.308-3.516 6.484 6.484 0 0 0-1.905 3.959c-.023.222-.014.442.025.654a4.97 4.97 0 0 1-2.07-.655ZM16.44 15.98a4.97 4.97 0 0 0 2.07-.654.78.78 0 0 0 .357-.442 3 3 0 0 0-4.308-3.517 6.484 6.484 0 0 1 1.907 3.96 2.32 2.32 0 0 1-.026.654ZM18 8a2 2 0 1 1-4 0 2 2 0 0 1 4 0ZM5.304 16.19a.844.844 0 0 1-.277-.71 5 5 0 0 1 9.947 0 .843.843 0 0 1-.277.71A6.975 6.975 0 0 1 10 18a6.974 6.974 0 0 1-4.696-1.81Z" />
</svg>
    Élèves
</a>
<a href="{{ route('teachers.index') }}" data-nav-item style="order: 5{{ Auth::user()->canAccess('teachers') ? '' : '; display:none' }}"
   class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('teachers.*') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100' }} transition">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
  <path fill-rule="evenodd" d="M9.664 1.319a.75.75 0 0 1 .672 0 41.059 41.059 0 0 1 8.198 5.424.75.75 0 0 1-.254 1.285 31.372 31.372 0 0 0-7.86 3.83.75.75 0 0 1-.84 0 31.508 31.508 0 0 0-2.08-1.287V9.394c0-.244.116-.463.302-.592a35.504 35.504 0 0 1 3.305-2.033.75.75 0 0 0-.714-1.319 37 37 0 0 0-3.446 2.12A2.216 2.216 0 0 0 6 9.393v.38a31.293 31.293 0 0 0-4.28-1.746.75.75 0 0 1-.254-1.285 41.059 41.059 0 0 1 8.198-5.424ZM6 11.459a29.848 29.848 0 0 0-2.455-1.158 41.029 41.029 0 0 0-.39 3.114.75.75 0 0 0 .419.74c.528.256 1.046.53 1.554.82-.21.324-.455.63-.739.914a.75.75 0 1 0 1.06 1.06c.37-.369.69-.77.96-1.193a26.61 26.61 0 0 1 3.095 2.348.75.75 0 0 0 .992 0 26.547 26.547 0 0 1 5.93-3.95.75.75 0 0 0 .42-.739 41.053 41.053 0 0 0-.39-3.114 29.925 29.925 0 0 0-5.199 2.801 2.25 2.25 0 0 1-2.514 0c-.41-.275-.826-.541-1.25-.797a6.985 6.985 0 0 1-1.084 3.45 26.503 26.503 0 0 0-1.281-.78A5.487 5.487 0 0 0 6 12v-.54Z" clip-rule="evenodd" />
</svg>
    Enseignants
</a>
<a href="{{ route('classes.index') }}" data-nav-item style="order: 3{{ Auth::user()->canAccess('classes') ? '' : '; display:none' }}"
   class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('classes.*') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100' }} transition">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
  <path fill-rule="evenodd" d="M3.5 2A1.5 1.5 0 0 0 2 3.5V4h16v-.5A1.5 1.5 0 0 0 16.5 2h-13ZM2 5.5h16v8A1.5 1.5 0 0 1 16.5 15H11v.6l2.2 2.4a.75.75 0 1 1-1.1 1L10 16.7 7.9 19a.75.75 0 1 1-1.1-1L9 15.6V15H3.5A1.5 1.5 0 0 1 2 13.5v-8Zm3.75 2a.75.75 0 0 0 0 1.5h4.5a.75.75 0 0 0 0-1.5h-4.5Zm0 3a.75.75 0 0 0 0 1.5h8.5a.75.75 0 0 0 0-1.5h-8.5Z" clip-rule="evenodd" />
</svg>
    Classes
</a>
<a href="{{ route('finances.index') }}" data-nav-item style="order: 8{{ Auth::user()->canAccess('finances') ? '' : '; display:none' }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('finances.*') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100' }} transition">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
  <path fill-rule="evenodd" d="M1 4a1 1 0 0 1 1-1h16a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V4Zm12 4a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM4 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm13-1a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM1.75 14.5a.75.75 0 0 0 0 1.5c4.417 0 8.693.603 12.749 1.73 1.111.309 2.251-.512 2.251-1.696v-.784a.75.75 0 0 0-1.5 0v.784a.272.272 0 0 1-.35.25A49.043 49.043 0 0 0 1.75 14.5Z" clip-rule="evenodd" />
</svg>
    Finances
</a>
<a href="{{ route('subjects.index') }}" data-nav-item style="order: 4{{ Auth::user()->canAccess('subjects') ? '' : '; display:none' }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('subjects.*') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100' }} transition">
   <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
  <path fill-rule="evenodd" d="M6 4.75A.75.75 0 0 1 6.75 4h10.5a.75.75 0 0 1 0 1.5H6.75A.75.75 0 0 1 6 4.75ZM6 10a.75.75 0 0 1 .75-.75h10.5a.75.75 0 0 1 0 1.5H6.75A.75.75 0 0 1 6 10Zm0 5.25a.75.75 0 0 1 .75-.75h10.5a.75.75 0 0 1 0 1.5H6.75a.75.75 0 0 1-.75-.75ZM1.99 4.75a1 1 0 0 1 1-1H3a1 1 0 0 1 1 1v.01a1 1 0 0 1-1 1h-.01a1 1 0 0 1-1-1v-.01ZM1.99 15.25a1 1 0 0 1 1-1H3a1 1 0 0 1 1 1v.01a1 1 0 0 1-1 1h-.01a1 1 0 0 1-1-1v-.01ZM1.99 10a1 1 0 0 1 1-1H3a1 1 0 0 1 1 1v.01a1 1 0 0 1-1 1h-.01a1 1 0 0 1-1-1V10Z" clip-rule="evenodd" />
</svg>
    Matières
</a>
<a href="{{ route('teaching-assignments.index') }}" data-nav-item style="order: 9{{ Auth::user()->canAccess('teaching-assignments') ? '' : '; display:none' }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('teaching-assignments.*') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100' }} transition">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
        <path d="M4 3.5A1.5 1.5 0 0 1 5.5 2h9A1.5 1.5 0 0 1 16 3.5v13a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 4 16.5v-13ZM7 5.75A.75.75 0 0 0 7.75 6.5h4.5a.75.75 0 0 0 0-1.5h-4.5A.75.75 0 0 0 7 5.75Zm0 4A.75.75 0 0 0 7.75 10.5h4.5a.75.75 0 0 0 0-1.5h-4.5a.75.75 0 0 0-.75.75Zm0 4a.75.75 0 0 0 .75.75h2.5a.75.75 0 0 0 0-1.5h-2.5a.75.75 0 0 0-.75.75Z" />
    </svg>
    Affectations
</a>
<a href="{{ route('timetable.index') }}" data-nav-item style="order: 7{{ Auth::user()->canAccess('timetable') ? '' : '; display:none' }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('timetable.*') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100' }} transition">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
  <path d="M10 9.25a.75.75 0 0 0-.75.75v.01c0 .414.336.75.75.75h.01a.75.75 0 0 0 .75-.75V10a.75.75 0 0 0-.75-.75H10ZM6 13.25a.75.75 0 0 0-.75.75v.01c0 .414.336.75.75.75h.01a.75.75 0 0 0 .75-.75V14a.75.75 0 0 0-.75-.75H6ZM8 13.25a.75.75 0 0 0-.75.75v.01c0 .414.336.75.75.75h.01a.75.75 0 0 0 .75-.75V14a.75.75 0 0 0-.75-.75H8ZM9.25 14a.75.75 0 0 1 .75-.75h.01a.75.75 0 0 1 .75.75v.01a.75.75 0 0 1-.75.75H10a.75.75 0 0 1-.75-.75V14ZM12 11.25a.75.75 0 0 0-.75.75v.01c0 .414.336.75.75.75h.01a.75.75 0 0 0 .75-.75V12a.75.75 0 0 0-.75-.75H12ZM12 13.25a.75.75 0 0 0-.75.75v.01c0 .414.336.75.75.75h.01a.75.75 0 0 0 .75-.75V14a.75.75 0 0 0-.75-.75H12ZM13.25 12a.75.75 0 0 1 .75-.75h.01a.75.75 0 0 1 .75.75v.01a.75.75 0 0 1-.75.75H14a.75.75 0 0 1-.75-.75V12ZM11.25 10.005c0-.417.338-.755.755-.755h2a.755.755 0 1 1 0 1.51h-2a.755.755 0 0 1-.755-.755ZM6.005 11.25a.755.755 0 1 0 0 1.51h4a.755.755 0 1 0 0-1.51h-4Z" />
  <path fill-rule="evenodd" d="M5.75 2a.75.75 0 0 1 .75.75V4h7V2.75a.75.75 0 0 1 1.5 0V4h.25A2.75 2.75 0 0 1 18 6.75v8.5A2.75 2.75 0 0 1 15.25 18H4.75A2.75 2.75 0 0 1 2 15.25v-8.5A2.75 2.75 0 0 1 4.75 4H5V2.75A.75.75 0 0 1 5.75 2Zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75Z" clip-rule="evenodd" />
</svg>
    Emploi du temps
</a>
<a href="{{ route('notes.index') }}" data-nav-item style="order: 10{{ Auth::user()->canAccess('notes') ? '' : '; display:none' }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('notes.*') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100' }} transition">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
  <path d="m5.433 13.917 1.262-3.155A4 4 0 0 1 7.58 9.42l6.92-6.918a2.121 2.121 0 0 1 3 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 0 1-.65-.65Z" />
  <path d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0 0 10 3H4.75A2.75 2.75 0 0 0 2 5.75v9.5A2.75 2.75 0 0 0 4.75 18h9.5A2.75 2.75 0 0 0 17 15.25V10a.75.75 0 0 0-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5Z" />
</svg>
    Notes et Bulletins
</a>
<a href="{{ route('parents.index') }}" data-nav-item style="order: 11{{ Auth::user()->canAccess('parents') ? '' : '; display:none' }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('parents.*') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100' }} transition">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
  <path fill-rule="evenodd" d="M2 3.5A1.5 1.5 0 0 1 3.5 2h1.148a1.5 1.5 0 0 1 1.465 1.175l.716 3.223a1.5 1.5 0 0 1-1.052 1.767l-.933.267c-.41.117-.643.555-.48.95a11.542 11.542 0 0 0 6.254 6.254c.395.163.833-.07.95-.48l.267-.933a1.5 1.5 0 0 1 1.767-1.052l3.223.716A1.5 1.5 0 0 1 18 15.352V16.5a1.5 1.5 0 0 1-1.5 1.5H15c-1.149 0-2.263-.15-3.326-.43A13.022 13.022 0 0 1 2.43 8.326 13.019 13.019 0 0 1 2 5V3.5Z" clip-rule="evenodd" />
</svg>
    Parents
</a>
<a href="{{ route('documents.index') }}" data-nav-item style="order: 12{{ Auth::user()->canAccess('documents') ? '' : '; display:none' }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('documents.*') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100' }} transition">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
  <path fill-rule="evenodd" d="M4.5 2A1.5 1.5 0 0 0 3 3.5v13A1.5 1.5 0 0 0 4.5 18h11a1.5 1.5 0 0 0 1.5-1.5V7.621a1.5 1.5 0 0 0-.44-1.06l-4.12-4.122A1.5 1.5 0 0 0 11.378 2H4.5Zm2.25 8.5a.75.75 0 0 0 0 1.5h6.5a.75.75 0 0 0 0-1.5h-6.5Zm0 3a.75.75 0 0 0 0 1.5h6.5a.75.75 0 0 0 0-1.5h-6.5Z" clip-rule="evenodd" />
</svg>
    Documents
</a>
<a href="{{ route('reports.index') }}" data-nav-item style="order: 13{{ Auth::user()->canAccess('reports') ? '' : '; display:none' }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('reports.*') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100' }} transition">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
  <path fill-rule="evenodd" d="M1 2.75A.75.75 0 0 1 1.75 2h16.5a.75.75 0 0 1 0 1.5H18v8.75A2.75 2.75 0 0 1 15.25 15h-1.072l.798 3.06a.75.75 0 0 1-1.452.38L13.41 18H6.59l-.114.44a.75.75 0 0 1-1.452-.38L5.823 15H4.75A2.75 2.75 0 0 1 2 12.25V3.5h-.25A.75.75 0 0 1 1 2.75ZM7.373 15l-.391 1.5h6.037l-.392-1.5H7.373Zm7.49-8.931a.75.75 0 0 1-.175 1.046 19.326 19.326 0 0 0-3.398 3.098.75.75 0 0 1-1.097.04L8.5 8.561l-2.22 2.22A.75.75 0 1 1 5.22 9.72l2.75-2.75a.75.75 0 0 1 1.06 0l1.664 1.663a20.786 20.786 0 0 1 3.122-2.74.75.75 0 0 1 1.046.176Z" clip-rule="evenodd" />
</svg>
    Rapports
</a>
@if(Auth::user()->canManageSchoolTeam())
<a href="{{ route('school.team.index') }}" data-nav-item style="order: 14" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('school.team.*') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100' }} transition">
 <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
  <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-5.5-2.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0ZM10 12a5.99 5.99 0 0 0-4.793 2.39A6.483 6.483 0 0 0 10 16.5a6.483 6.483 0 0 0 4.793-2.11A5.99 5.99 0 0 0 10 12Z" clip-rule="evenodd" />
</svg>
    Partager l’espace
</a>
@endif
@if(Auth::user()->canManageSchoolSettings())
<a href="{{ route('school.settings.index') }}" data-nav-item style="order: 15" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('school.settings.*') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100' }} transition">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
  <path fill-rule="evenodd" d="M8.34 1.804A1 1 0 0 1 9.32 1h1.36a1 1 0 0 1 .98.804l.295 1.473c.497.144.971.342 1.416.587l1.25-.834a1 1 0 0 1 1.262.125l.962.962a1 1 0 0 1 .125 1.262l-.834 1.25c.245.445.443.919.587 1.416l1.473.294a1 1 0 0 1 .804.98v1.361a1 1 0 0 1-.804.98l-1.473.295a6.95 6.95 0 0 1-.587 1.416l.834 1.25a1 1 0 0 1-.125 1.262l-.962.962a1 1 0 0 1-1.262.125l-1.25-.834a6.953 6.953 0 0 1-1.416.587l-.294 1.473a1 1 0 0 1-.98.804H9.32a1 1 0 0 1-.98-.804l-.295-1.473a6.957 6.957 0 0 1-1.416-.587l-1.25.834a1 1 0 0 1-1.262-.125l-.962-.962a1 1 0 0 1-.125-1.262l.834-1.25a6.957 6.957 0 0 1-.587-1.416l-1.473-.294A1 1 0 0 1 1 10.68V9.32a1 1 0 0 1 .804-.98l1.473-.295c.144-.497.342-.971.587-1.416l-.834-1.25a1 1 0 0 1 .125-1.262l.962-.962A1 1 0 0 1 5.38 3.03l1.25.834a6.957 6.957 0 0 1 1.416-.587l.294-1.473ZM13 10a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" clip-rule="evenodd" />
</svg>
    Paramètres
</a>
@endif
<a href="{{ route('updates.index') }}" data-nav-item style="order: 16{{ Auth::user()->canAccess('updates') ? '' : '; display:none' }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('updates.*') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100' }} transition">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5" aria-hidden="true">
  <path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 0 1-9.201 2.466l-.312-.311h2.433a.75.75 0 0 0 0-1.5H3.989a.75.75 0 0 0-.75.75v4.242a.75.75 0 0 0 1.5 0v-2.43l.31.31a7 7 0 0 0 11.712-3.138.75.75 0 0 0-1.449-.39Zm1.23-3.723a.75.75 0 0 0 .219-.53V2.929a.75.75 0 0 0-1.5 0V5.36l-.31-.31A7 7 0 0 0 3.239 8.188a.75.75 0 1 0 1.448.389A5.5 5.5 0 0 1 13.89 6.11l.311.31h-2.432a.75.75 0 0 0 0 1.5h4.243a.75.75 0 0 0 .53-.219Z" clip-rule="evenodd" />
</svg>
    Mise à Jour
</a>
@endif
</nav>
</div>
<!-- Profil d'utilisateur -->
 <div class="shrink-0 p-4 border-t border-slate-100 flex items-center justify-between bg-white">
    <div>
        <p class="text-xs font-semibold text-slate-800">{{ Auth::user()->name }}</p>
        <p class="text-[10px] text-slate-400">
            @if(Auth::user()->isSuperAdmin())
                Super-administrateur
            @else
                {{ Auth::user()->roleLabel() }}
            @endif
        </p>
   </div>
   <form method="POST" action="{{ route('logout') }}" class="flex items-center">
       @csrf
       <button type="submit" class="inline-flex items-center gap-2 text-xs text-slate-500 hover:text-red-600 transition">
           <span>Déconnexion</span>
           <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
               <path fill-rule="evenodd" d="M17 4.25A2.25 2.25 0 0 0 14.75 2h-5.5A2.25 2.25 0 0 0 7 4.25v2a.75.75 0 0 0 1.5 0v-2a.75.75 0 0 1 .75-.75h5.5a.75.75 0 0 1 .75.75v11.5a.75.75 0 0 1-.75.75h-5.5a.75.75 0 0 1-.75-.75v-2a.75.75 0 0 0-1.5 0v2A2.25 2.25 0 0 0 9.25 18h5.5A2.25 2.25 0 0 0 17 15.75V4.25Z" clip-rule="evenodd" />
               <path fill-rule="evenodd" d="M14 10a.75.75 0 0 0-.75-.75H3.704l1.048-.943a.75.75 0 1 0-1.004-1.114l-2.5 2.25a.75.75 0 0 0 0 1.114l2.5 2.25a.75.75 0 1 0 1.004-1.114l-1.048-.943h9.546A.75.75 0 0 0 14 10Z" clip-rule="evenodd" />
           </svg>
       </button>
   </form>
</div>
</aside>

<!-- Zone de contenu principale à droite -->
<main class="h-screen min-h-0 min-w-0 flex-1 overflow-y-auto p-6">
   @unless(Auth::user()->isSuperAdmin())
   <div class="relative mb-4 max-w-md" id="global-search">
       <label for="global-search-input" class="sr-only">Rechercher un onglet</label>
       <input id="global-search-input" type="text" autocomplete="off" placeholder="Rechercher un onglet (Élèves, Finances…)" class="w-full rounded-xl border-slate-300 bg-white py-2 pl-3 pr-3 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
       <ul id="global-search-results" class="absolute z-30 mt-1 hidden max-h-72 w-full overflow-y-auto rounded-xl border border-slate-200 bg-white py-1 text-sm shadow-lg"></ul>
   </div>
   @endunless
   {{ $slot }}
</main>
</div>
<style>
    input[type="search"]::-webkit-search-cancel-button,
    input[type="search"]::-webkit-search-decoration { -webkit-appearance: none; appearance: none; display: none; }
    input[type="search"]::-ms-clear { display: none; }
</style>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('form input[type="search"][name="search"]').forEach((input) => {
            const form = input.form;
            let timer;
            const initial = input.value;
            input.setAttribute('autocomplete', 'off');

            if (initial !== '') {
                input.focus();
                input.setSelectionRange(initial.length, initial.length);
            }

            input.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    if (input.value.trim() !== initial.trim()) form.requestSubmit();
                }, 450);
            });

            input.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && input.value !== '') {
                    input.value = '';
                    clearTimeout(timer);
                    form.requestSubmit();
                }
            });
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('global-search-input');
        if (!input) return;
        const list = document.getElementById('global-search-results');
        const norm = (s) => s.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim();
        const items = [...document.querySelectorAll('[data-nav-item]')]
            .filter((a) => a.style.display !== 'none')
            .map((a) => ({ label: a.textContent.trim(), href: a.href }));
        let matches = [];
        let active = 0;

        const render = () => {
            list.innerHTML = '';
            matches.forEach((m, i) => {
                const li = document.createElement('li');
                const a = document.createElement('a');
                a.href = m.href;
                a.textContent = m.label;
                a.className = 'block px-3 py-2 ' + (i === active ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-slate-50');
                li.appendChild(a);
                list.appendChild(li);
            });
            if (!matches.length && input.value.trim()) {
                list.innerHTML = '<li class="px-3 py-2 text-slate-500">Aucun onglet trouvé</li>';
            }
            list.classList.toggle('hidden', !input.value.trim());
        };

        input.addEventListener('input', () => {
            const q = norm(input.value);
            matches = q ? items.filter((m) => norm(m.label).includes(q)) : [];
            active = 0;
            render();
        });
        input.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowDown' && matches.length) { active = (active + 1) % matches.length; render(); e.preventDefault(); }
            if (e.key === 'ArrowUp' && matches.length) { active = (active - 1 + matches.length) % matches.length; render(); e.preventDefault(); }
            if (e.key === 'Enter' && matches[active]) { window.location.href = matches[active].href; e.preventDefault(); }
            if (e.key === 'Escape') { input.value = ''; matches = []; render(); }
        });
        document.addEventListener('click', (e) => { if (!e.target.closest('#global-search')) list.classList.add('hidden'); });
    });
</script>
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('{{ asset('sw.js') }}')
                .catch((error) => console.error('Échec de l’enregistrement du Service Worker :', error));
        });
    }
</script>
@livewireScripts
</body>
</html>
