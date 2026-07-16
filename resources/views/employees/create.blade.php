@extends('layouts.app')

@section('content')

<div class="max-w-7xl mx-auto">

    <h1 class="text-3xl font-bold mb-8">
        Add Employee
    </h1>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-300 text-red-700 p-4 rounded mb-6">
            <ul class="list-disc ml-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('employees.store') }}"
          method="POST"
          enctype="multipart/form-data">

        @csrf

        <div class="grid grid-cols-2 gap-6">

            <div>
                <label class="block mb-2 font-medium">Department</label>

                <select name="department_id" class="w-full border rounded p-3">

                    <option value="">Select Department</option>

                    @foreach($departments as $department)

                        <option value="{{ $department->id }}"
                            {{ old('department_id') == $department->id ? 'selected' : '' }}>
                            {{ $department->name }}
                        </option>

                    @endforeach

                </select>
            </div>

            <div>
                <label class="block mb-2 font-medium">Employee Name</label>

                <input type="text"
                       name="name"
                       value="{{ old('name') }}"
                       class="w-full border rounded p-3">
            </div>

            <div>
                <label class="block mb-2 font-medium">Email</label>

                <input type="email"
                       name="email"
                       value="{{ old('email') }}"
                       class="w-full border rounded p-3">
            </div>

            <div>
                <label class="block mb-2 font-medium">Phone</label>

                <input type="text"
                       name="phone"
                       value="{{ old('phone') }}"
                       class="w-full border rounded p-3">
            </div>

            <div>
                <label class="block mb-2 font-medium">Designation</label>

                <input type="text"
                       name="designation"
                       value="{{ old('designation') }}"
                       class="w-full border rounded p-3">
            </div>

            <div>
                <label class="block mb-2 font-medium">Salary</label>

                <input type="number"
                       name="salary"
                       value="{{ old('salary') }}"
                       class="w-full border rounded p-3">
            </div>

            <div>
                <label class="block mb-2 font-medium">Joining Date</label>

                <input type="date"
                       name="joining_date"
                       value="{{ old('joining_date') }}"
                       class="w-full border rounded p-3">
            </div>

            <div>
                <label class="block mb-2 font-medium">Date of Birth</label>

                <input type="date"
                       name="date_of_birth"
                       value="{{ old('date_of_birth') }}"
                       class="w-full border rounded p-3">
            </div>

            <div>
                <label class="block mb-2 font-medium">Gender</label>

                <select name="gender"
                        class="w-full border rounded p-3">

                    <option value="">Select Gender</option>

                    <option value="Male" {{ old('gender')=='Male' ? 'selected' : '' }}>Male</option>
                    <option value="Female" {{ old('gender')=='Female' ? 'selected' : '' }}>Female</option>
                    <option value="Other" {{ old('gender')=='Other' ? 'selected' : '' }}>Other</option>

                </select>
            </div>

            <div>
                <label class="block mb-2 font-medium">Blood Group</label>

                <input type="text"
                       name="blood_group"
                       value="{{ old('blood_group') }}"
                       class="w-full border rounded p-3">
            </div>

            <div>
                <label class="block mb-2 font-medium">Emergency Contact</label>

                <input type="text"
                       name="emergency_contact"
                       value="{{ old('emergency_contact') }}"
                       class="w-full border rounded p-3">
            </div>

            <div>
                <label class="block mb-2 font-medium">Photo</label>

                <input type="file"
                       name="photo"
                       class="w-full border rounded p-3">
            </div>

        </div>

        <div class="mt-6">

            <label class="block mb-2 font-medium">
                Address
            </label>

            <textarea
                name="address"
                rows="4"
                class="w-full border rounded p-3">{{ old('address') }}</textarea>

        </div>

        <div class="mt-6">

            <label class="block mb-2 font-medium">
                Status
            </label>

            <select name="status"
                    class="w-full border rounded p-3">

                <option value="1" {{ old('status',1)==1 ? 'selected' : '' }}>Active</option>
                <option value="0" {{ old('status')==='0' ? 'selected' : '' }}>Inactive</option>

            </select>

        </div>

        <button
            type="submit"
            class="mt-8 bg-green-600 text-white px-8 py-3 rounded hover:bg-green-700">

            Save Employee

        </button>

    </form>

</div>

@endsection