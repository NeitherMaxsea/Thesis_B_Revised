@extends('layout.admin')

@php
    $generalDisabilityCategories = array_keys($disabilityCategories);
@endphp

@section('breadcrumb')
    <i data-lucide="house"></i>
    <i data-lucide="chevron-right"></i>
    <span>User Management</span>
    <i data-lucide="chevron-right"></i>
    <strong>Create User</strong>
@endsection

@section('content')
<section class="admin-create-user" data-admin-create-user>
    <div class="admin-create-tabs" role="tablist" aria-label="Account type">
        <button type="button" class="is-active" data-admin-create-tab="applicant" role="tab" aria-selected="true">Applicant</button>
        <button type="button" data-admin-create-tab="business" role="tab" aria-selected="false">Business</button>
    </div>

    @if ($errors->any())
        <div class="admin-create-alert" role="alert">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data" class="admin-create-card" data-admin-create-panel="applicant" data-admin-disability-form data-disability-categories='@json($disabilityCategories)'>
        @csrf
        <input type="hidden" name="account_type" value="pwd_applicant">
        <header>
            <h1>PWD Applicant Profile</h1>
            <p>Enter applicant details to create a managed PWD applicant account.</p>
        </header>

        <div class="admin-create-grid admin-create-grid--three">
            <label><span>General Disability Category <b>*</b></span><select name="disability" data-admin-general-disability required><option value="">Select general disability category</option>@foreach ($generalDisabilityCategories as $category)<option value="{{ $category }}" @selected(old('disability') === $category)>{{ $category }}</option>@endforeach</select></label>
            <label><span>Disability Category <b>*</b></span><select name="disability_category" data-admin-disability-category data-selected-disability-category="{{ old('disability_category') }}" required disabled><option value="">Select general category first</option></select></label>
            <label class="admin-create-grid__span-two"><span>Email Address <b>*</b></span><input type="email" name="email" placeholder="Enter email address" required></label>
            <label><span>First Name <b>*</b></span><input name="first_name" placeholder="Enter first name" required></label>
            <label><span>Middle Name <em>(Optional)</em></span><input name="middle_name" placeholder="Enter middle name"></label>
            <label><span>Last Name <b>*</b></span><input name="last_name" placeholder="Enter last name" required></label>
            <label class="admin-create-grid__full"><span>Present Address <b>*</b></span><input name="street_address" placeholder="Enter present address" required></label>
            <label class="admin-create-grid__full"><span>Provincial Address <b>*</b></span><input name="provincial_address" placeholder="Enter provincial address" required></label>
            <label><span>Telephone No. <em>(Optional)</em></span><input name="telephone" placeholder="Enter telephone number"></label>
            <label class="admin-create-grid__span-two"><span>Phone No. <b>*</b></span><input name="contact_number" placeholder="Enter phone number" required></label>
            <label><span>Date of Birth <b>*</b></span><input type="date" name="birthdate" required></label>
            <label><span>Place of Birth</span><input name="place_of_birth" placeholder="Enter place of birth"></label>
            <label><span>Age <b>*</b></span><input type="number" name="age" min="15" max="100" placeholder="Enter age" required></label>
            <label class="admin-create-file"><span>PWD ID Front</span><input type="file" name="pwd_id" accept=".jpg,.jpeg,.png,.pdf"><small>Upload the front side of the PWD ID.</small></label>
            <label class="admin-create-file admin-create-grid__span-two"><span>PWD ID Back</span><input type="file" name="pwd_id_back" accept=".jpg,.jpeg,.png,.pdf"><small>Upload the back side of the PWD ID.</small></label>
            <label class="admin-create-grid__full"><span>Password <b>*</b></span><input type="password" name="password" placeholder="Enter password" minlength="8" required></label>
        </div>

        <footer><button type="reset" class="admin-create-secondary">Clear</button><button type="submit" class="admin-create-primary"><i data-lucide="user-round-plus"></i>Create User</button></footer>
    </form>

    <form action="{{ route('admin.users.store') }}" method="POST" class="admin-create-card" data-admin-create-panel="business" hidden>
        @csrf
        <input type="hidden" name="account_type" value="employer">
        <header>
            <h1>Business Account Sheet</h1>
            <p>Enter business details to create a managed business account.</p>
        </header>

        <div class="admin-create-grid admin-create-grid--three">
            <label><span>Business Name <b>*</b></span><input name="business_name" placeholder="Enter business name" required></label>
            <label><span>Industry <b>*</b></span><select name="industry" required><option value="">Select industry</option><option>Retail</option><option>Food Service</option><option>Information Technology</option><option>Manufacturing</option><option>Professional Services</option></select></label>
            <label><span>Contact Number <b>*</b></span><input name="contact_number" placeholder="Enter contact number" required></label>
            <label class="admin-create-grid__span-two"><span>Email Address <b>*</b></span><input type="email" name="email" placeholder="Enter email address" required></label>
            <label><span>Password <b>*</b></span><input type="password" name="password" placeholder="Enter password" minlength="8" required></label>
        </div>

        <footer><button type="reset" class="admin-create-secondary">Clear</button><button type="submit" class="admin-create-primary"><i data-lucide="user-round-plus"></i>Create User</button></footer>
    </form>
</section>
@endsection
