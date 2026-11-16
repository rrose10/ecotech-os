<div class="grid grid-cols-2 gap-6">


    <div>
        <label class="block mb-2 font-medium">Department</label>

        <select name="department_id" class="w-full border rounded p-3">

            <option value="">
                Select Department
            </option>


            @foreach($departments as $department)

                <option value="{{ $department->id }}"
                    {{ old('department_id', $employee->department_id ?? '') == $department->id ? 'selected' : '' }}>

                    {{ $department->name }}

                </option>

            @endforeach

        </select>
    </div>





    <div>
        <label class="block mb-2 font-medium">
            Team Member Name
        </label>


        <input
            type="text"
            name="name"
            value="{{ old('name', $employee->name ?? '') }}"
            class="w-full border rounded p-3">

    </div>





    <div>
        <label class="block mb-2 font-medium">
            Email
        </label>


        <input
            type="email"
            name="email"
            value="{{ old('email', $employee->email ?? '') }}"
            class="w-full border rounded p-3">

    </div>






    {{-- Login Password Only For New Team Member --}}

    @if(!isset($employee))


    <div>

        <label class="block mb-2 font-medium">
            Login Password
        </label>


        <input
            type="password"
            name="password"
            class="w-full border rounded p-3"
            placeholder="Enter login password">

    </div>





    <div>

        <label class="block mb-2 font-medium">
            Confirm Password
        </label>


        <input
            type="password"
            name="password_confirmation"
            class="w-full border rounded p-3"
            placeholder="Confirm password">

    </div>


    @endif







    <div>
        <label class="block mb-2 font-medium">
            Phone
        </label>


        <input
            type="text"
            name="phone"
            value="{{ old('phone', $employee->phone ?? '') }}"
            class="w-full border rounded p-3">

    </div>







    <div>
        <label class="block mb-2 font-medium">
            Designation
        </label>


        <input
            type="text"
            name="designation"
            value="{{ old('designation', $employee->designation ?? '') }}"
            class="w-full border rounded p-3">

    </div>







    <div>
        <label class="block mb-2 font-medium">
            Basic Salary
        </label>


        <input
            type="number"
            name="basic_salary"
            step="0.01"
            min="0"
            value="{{ old('basic_salary', $employee->basic_salary ?? '') }}"
            class="w-full border rounded p-3">

    </div>







    <div>
        <label class="block mb-2 font-medium">
            Joining Date
        </label>


        <input
            type="date"
            name="joining_date"
            value="{{ old('joining_date', isset($employee) && $employee->joining_date ? $employee->joining_date->format('Y-m-d') : '') }}"
            class="w-full border rounded p-3">

    </div>







    <div>
        <label class="block mb-2 font-medium">
            Date of Birth
        </label>


        <input
            type="date"
            name="date_of_birth"
            value="{{ old('date_of_birth', isset($employee) && $employee->date_of_birth ? $employee->date_of_birth->format('Y-m-d') : '') }}"
            class="w-full border rounded p-3">

    </div>







    <div>
        <label class="block mb-2 font-medium">
            Gender
        </label>


        <select name="gender" class="w-full border rounded p-3">


            <option value="">
                Select Gender
            </option>


            <option value="Male"
                {{ old('gender', $employee->gender ?? '') == 'Male' ? 'selected' : '' }}>

                Male

            </option>



            <option value="Female"
                {{ old('gender', $employee->gender ?? '') == 'Female' ? 'selected' : '' }}>

                Female

            </option>



            <option value="Other"
                {{ old('gender', $employee->gender ?? '') == 'Other' ? 'selected' : '' }}>

                Other

            </option>


        </select>

    </div>







    <div>
        <label class="block mb-2 font-medium">
            Blood Group
        </label>


        <input
            type="text"
            name="blood_group"
            value="{{ old('blood_group', $employee->blood_group ?? '') }}"
            class="w-full border rounded p-3">

    </div>







    <div>
        <label class="block mb-2 font-medium">
            Emergency Contact
        </label>


        <input
            type="text"
            name="emergency_contact"
            value="{{ old('emergency_contact', $employee->emergency_contact ?? '') }}"
            class="w-full border rounded p-3">

    </div>







    <div>

        <label class="block mb-2 font-medium">
            Photo
        </label>


        <input
            type="file"
            name="photo"
            class="w-full border rounded p-3">



        @if(isset($employee) && $employee->photo)

            <img
                src="{{ asset('storage/'.$employee->photo) }}"
                class="mt-2 h-20 rounded">

        @endif


    </div>



</div>







<div class="mt-6">


    <label class="block mb-2 font-medium">
        Address
    </label>



    <textarea
        name="address"
        rows="4"
        class="w-full border rounded p-3">{{ old('address', $employee->address ?? '') }}</textarea>


</div>







<div class="mt-6">


    <label class="block mb-2 font-medium">
        Status
    </label>



    <select name="status" class="w-full border rounded p-3">


        <option value="1"
            {{ old('status', $employee->status ?? 1) == 1 ? 'selected' : '' }}>

            Active

        </option>



        <option value="0"
            {{ old('status', $employee->status ?? 1) == 0 ? 'selected' : '' }}>

            Inactive

        </option>


    </select>


</div>






<button
    type="submit"
    class="mt-8 bg-green-600 text-white px-8 py-3 rounded hover:bg-green-700">


    {{ isset($employee) ? 'Update Team Member' : 'Save Team Member' }}


</button>