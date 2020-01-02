<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => 'The :attribute must be accepted.',
    'active_url'           => 'The :attribute is not a valid URL.',
    'after'                => 'The :attribute must be a date after :date.',
    'after_or_equal'       => 'The :attribute must be a date after or equal to :date.',
    'alpha'                => 'The :attribute may only contain letters.',
    'alpha_dash'           => 'The :attribute may only contain letters, numbers, dashes and underscores.',
    'alpha_num'            => 'The :attribute may only contain letters and numbers.',
    'array'                => 'The :attribute must be an array.',
    'before'               => 'The :attribute must be a date before :date.',
    'before_or_equal'      => 'The :attribute must be a date before or equal to :date.',
    'between'              => [
        'numeric' => 'The :attribute must be between :min and :max.',
        'file'    => 'The :attribute must be between :min and :max kilobytes.',
        'string'  => 'The :attribute must be between :min and :max characters.',
        'array'   => 'The :attribute must have between :min and :max items.',
    ],
    'boolean'              => 'The :attribute field must be true or false.',
    'confirmed'            => 'The :attribute confirmation does not match.',
    'date'                 => 'The :attribute is not a valid date.',
    'date_format'          => 'The :attribute does not match the format :format.',
    'different'            => 'The :attribute and :other must be different.',
    'digits'               => 'The :attribute must be :digits digits.',
    'digits_between'       => 'The :attribute must be between :min and :max digits.',
    'dimensions'           => 'The :attribute has invalid image dimensions.',
    'distinct'             => 'The :attribute field has a duplicate value.',
    'email'                => 'The :attribute must be a valid email address.',
    'exists'               => 'The selected :attribute is invalid.',
    'file'                 => 'The :attribute must be a file.',
    'filled'               => 'The :attribute field must have a value.',
    'gt'                   => [
        'numeric' => 'The :attribute must be greater than :value.',
        'file'    => 'The :attribute must be greater than :value kilobytes.',
        'string'  => 'The :attribute must be greater than :value characters.',
        'array'   => 'The :attribute must have more than :value items.',
    ],
    'gte'                  => [
        'numeric' => 'The :attribute must be greater than or equal :value.',
        'file'    => 'The :attribute must be greater than or equal :value kilobytes.',
        'string'  => 'The :attribute must be greater than or equal :value characters.',
        'array'   => 'The :attribute must have :value items or more.',
    ],
    'image'                => 'The :attribute must be an image.',
    'in'                   => 'The selected :attribute is invalid.',
    'in_array'             => 'The :attribute field does not exist in :other.',
    'integer'              => 'The :attribute must be an integer.',
    'ip'                   => 'The :attribute must be a valid IP address.',
    'ipv4'                 => 'The :attribute must be a valid IPv4 address.',
    'ipv6'                 => 'The :attribute must be a valid IPv6 address.',
    'json'                 => 'The :attribute must be a valid JSON string.',
    'lt'                   => [
        'numeric' => 'The :attribute must be less than :value.',
        'file'    => 'The :attribute must be less than :value kilobytes.',
        'string'  => 'The :attribute must be less than :value characters.',
        'array'   => 'The :attribute must have less than :value items.',
    ],
    'lte'                  => [
        'numeric' => 'The :attribute must be less than or equal :value.',
        'file'    => 'The :attribute must be less than or equal :value kilobytes.',
        'string'  => 'The :attribute must be less than or equal :value characters.',
        'array'   => 'The :attribute must not have more than :value items.',
    ],
    'max'                  => [
        'numeric' => 'The :attribute may not be greater than :max.',
        'file'    => 'The :attribute may not be greater than :max kilobytes.',
        'string'  => 'The :attribute may not be greater than :max characters.',
        'array'   => 'The :attribute may not have more than :max items.',
    ],
    'mimes'                => 'The :attribute must be a file of type: :values.',
    'mimetypes'            => 'The :attribute must be a file of type: :values.',
    'min'                  => [
        'numeric' => 'The :attribute must be at least :min.',
        'file'    => 'The :attribute must be at least :min kilobytes.',
        'string'  => 'The :attribute must be at least :min characters.',
        'array'   => 'The :attribute must have at least :min items.',
    ],
    'not_in'               => 'The selected :attribute is invalid.',
    'not_regex'            => 'The :attribute format is invalid.',
    'numeric'              => 'The :attribute must be a number.',
    'present'              => 'The :attribute field must be present.',
    'regex'                => 'The :attribute format is invalid.',
    'required'             => 'The :attribute field is required.',
    'required_if'          => 'The :attribute field is required when :other is :value.',
    'required_unless'      => 'The :attribute field is required unless :other is in :values.',
    'required_with'        => 'The :attribute field is required when :values is present.',
    'required_with_all'    => 'The :attribute field is required when :values is present.',
    'required_without'     => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same'                 => 'The :attribute and :other must match.',
    'size'                 => [
        'numeric' => 'The :attribute must be :size.',
        'file'    => 'The :attribute must be :size kilobytes.',
        'string'  => 'The :attribute must be :size characters.',
        'array'   => 'The :attribute must contain :size items.',
    ],
    'string'               => 'The :attribute must be a string.',
    'timezone'             => 'The :attribute must be a valid zone.',
    'unique'               => 'The :attribute has already been taken.',
    'uploaded'             => 'The :attribute failed to upload.',
    'url'                  => 'The :attribute format is invalid.',
    'today'                  => 'The :attribute format is invalid.',
    'old_password' => 'You cannot set :attribute from last 3 passwords.',
    'patient_unique_info' => 'The :attribute has already been taken.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        //custom validation messages
        'assigned_chw' => [
            'chw_user' => 'Invalid CHW user selected.',
        ],
        'assigned_cm' => [
            'cm_user' => 'Invalid CM user selected.',
        ],
        'assigned_md' => [
            'md_user' => 'Invalid MD user selected.',
        ],

        'password_confirmation' => [
            'required' => 'Confirm your password.',
        ],
        'first_name' => [
            'required' => 'Enter first name.',
            'max' => 'The first name may not be greater than 20 characters.',
            'regex' => "Only special characters - ' . and alphabets are allowed.",
        ],
        'middle_initial' => [
            'max' => 'The middle initial may not be greater than 1 character.',
            'alpha' => 'Middle initial can only be letter.',
        ],
        'patient_alias' => [
            'max' => 'The patient alias may not be greater than 50 characters.',
        ],
        'last_name' => [
            'required' => 'Enter last name.',
            'max' => 'The last name may not be greater than 40 characters.',
            'regex' => "Only special characters - ' . and alphabets are allowed.",
        ],
        'dob' => [
            'required' => 'Choose a date.',
            'before' => 'Age must not be less than 10 years.',
            'after' => "Age must not be greater than 100 years.",
            'date_format' => "Choose a valid format date. Eg: 12-31-1970.",
        ],
        'ssn' => [
            'required' => 'Enter SSN number.',
            'patient_unique_info' => 'This SSN already exists in the system.',
            'max' => "The SSN may not be greater than 9 characters.",
            'regex' => "Enter valid SSN.",
        ],
        'language' => [
            'required' => 'Select Language.',
        ],
        'county' => [
            'required' => 'Select County.',
        ],
        'referral_source' => [
            'required' => 'Select Referral Source.',
            'exists' => 'Selected Referral Source is not valid, please choose different one.',
        ],
        'image' => [
             'image' => 'The image must be an image.',
             'mimes' => 'The image can be in JPG and PNG format.',
             'max' => 'The image size may not be greater than 5 MB.',
        ],
        'contact_title' => [
            'max' => 'The contact title may not be greater than 10 characters.',
        ],
        'emergency_person1_name' => [
            'required' => 'Enter name.',
            'max' => 'The emergency person name may not be greater than 60 characters.',
            'regex' => "Only special characters - ' . and alphabets are allowed.",
        ],
        'emergency_person2_name' => [
            'max' => 'The emergency person name may not be greater than 60 characters.',
            'regex' => "Only special characters - ' . and alphabets are allowed.",
        ],
        'emergency_person1_address' => [
            'required' => 'Enter Address Line 1',
            'max' => 'The address line 1 may not be greater than 100 characters.',
        ],
        'emergency_person2_address' => [
            'max' => 'The address line 1 may not be greater than 100 characters.',
        ],
        'emergency_person1_address2' => [
            'max' => 'The address line 2 may not be greater than 100 characters.',
        ],
        'emergency_person2_address2' => [
            'max' => 'The address Line 2 may not be greater than 100 characters.',
        ],
        'emergency_person1_phone' => [
            'required' => 'Enter phone.',
            'phone' => 'Enter valid phone number.',
        ],
        'emergency_person2_phone' => [
            'phone' => 'Enter valid phone number.',
        ],
        'emergency_person1_zip' => [
            'required' =>'Enter zip code.',
            'min' => 'The zip code be at least 5 numbers.',
            'max' => 'The zip code may not be greater than 5 numbers.',
            'regex' => "Only numbers are allowed in the zip code",
        ],
        'emergency_person2_zip' => [
            'min' => 'The zip code be at least 5 numbers.',
            'max' => 'The zip code may not be greater than 5 numbers.',
            'regex' => "Only numbers are allowed in the zip code",
        ],
        'emergency_person1_city' => [
            'required' => 'Enter city.',
            'max' => 'The city may not be greater than 50 characters.',
        ],
        'emergency_person1_state_id' => [
            'required' => 'Select state.',
            'exists' => 'Selected state does not exist, please choose different one.'
        ],
        'emergency_person1_relation' => [
            'required' =>'Choose a relationship.',
            'in' =>'Relationship is not valid.',
        ],
        'emergency_person2_relation' => [
            'in' =>'Relationship is not valid.',
        ],
        'emergency_person2_city' => [
            'max' => 'The city may not be greater than 50 characters.',
        ],
        'org_name' => [
            'max' => 'The organization name may not be greater than 100 characters.',
             'required' => 'Enter organization name.',
             'regex' => "Only numbers, special characters - ' . and alphabets are allowed.",
        ],
        'code' => [
            'max' => 'The code may not be greater than 10 characters.',
        ],
        'fax' => [
            'max' => 'The fax may not be greater than 10 characters.',
            'regex' => "Only Numbers are allowed in the Fax.",
        ],
        'contact_fax' => [
            'max' => 'The fax may not be greater than 10 characters.',
            'regex' => "Only Numbers are allowed in the Fax.",
        ],
        'address_line1' => [
            'required' =>'Enter address Line 1.',
            'max' => 'The address Line 1 may not be greater than 100 characters.',
        ],
        'address_line2' => [
            'max' => 'The Address Line 2 may not be greater than 100 characters.',
        ],
        'web_address' => [
            'max' => 'The web address may not be greater than 100 characters.',
        ],
        'contact_name' => [
            'max' => 'The contact name may not be greater than 60 characters.',
        ],
        'email' => [
            'max' => 'The email may not be greater than 45 characters.',
            'email' => 'Enter valid Email.',
            'unique' => 'This email is not available.',
        ],
        'zip_code' => [
            'required' =>'Enter Zipcode.',
            'min' => 'The Zipcode be at least 5 numbers.',
            'max' => 'The Zipcode may not be greater than 5 numbers.',
            'regex' => "Only Numbers are allowed in the Zipcode.",
        ],
        'phone' => [
            'phone' => 'Enter valid phone number.',
            'required' => 'Enter phone number.',
        ],
        'contact_email' => [
            'max' => 'The contact email may not be greater than 45 characters.',
            'email' => 'Enter valid contact email.',
            'unique' => 'This email has already been taken.'
        ],
        'living_with_other_text' => [
            'max' => 'Maximum 100 characters are allowed.',
            'required' => 'Please enter value.',
        ],

        //---------Healthcare Tab----------------
        'patient_concern' => [
            'required' => 'Please select at least one concern for patient.',
        ],
        'patient_concern_other_text' => [
            'required' => 'Please enter value.',
            'max' => 'Maximum 100 characters are allowed.',
        ],

        //--------------third tab started here ------------------------------
        'authorization_code' => [
            'required' => 'Enter WellPop authorization code.',
            'required_if' => 'Enter Wellpop authorization code.',
        ],

        //---------Dcument tab ---------------------

        'category_id' => [
            'required' => 'Choose document category.',
        ],
        'document_name' => [
            'required' => 'Enter document name.',
            'max' => 'Document name may not be greater than 60 characters.',
        ],
        'uploaded_document' => [
            'required' => 'Choose a file.',
            'mimes' => 'Document must be a PDF.',
            'max' => 'Document size may not be greater than 25 MB.',
        ],
                
        //---------Note tab ---------------------

        'notes_area' => [
            'required' => 'Enter an area.',
            'max' => 'Note area may not be greater than 100 characters.',
        ],
        'notes_subject' => [
            'required' => 'Add subject.',
            'max' => 'Note subject may not be greater than 1000 characters.',
        ],


        //-----------------Add New Referral Pop-Up------------------
        'city' => [
            'max' => 'The city name may not be greater than 50 characters.',
            'required' => 'Enter city.',
        ],
        'state_id' => [
             'required' => 'Select state.',
             'exists' => 'Selected state does not exist, please choose different one.'
        ],
        'web_address' => [
             'regex' => 'Enter a valid URL. Eg:- http://xyz.com',
             //'exists' => 'w does not exist, please choose different'
        ],
        'contact_name' => [
             'regex' => "Only special characters - ' . and alphabets are allowed."
        ],
        'contact_title' => [
             'regex' => "Only special characters - ' . and alphabets are allowed."
        ],
        
        //-----------------Add New PCp Pop-Up------------------
        'doctor_name' => [
            'max' => 'The doctor name may not be greater than 60 characters.',
            'required' => 'Enter doctor name.',
        ],
        'speciality' => [
             'required' => 'Select specialization.',
             'exists' => 'Selected speciality does not exist , please choose different one.'
        ],
        'phone_number' => [
             'required' => 'Enter phone number.',
             'phone' => 'Enter valid phone number.'
        ],
        'email' => [
             'required' => 'Enter email.',
             'email' => 'Enter valid email.',
             'unique' => 'This email has already been taken.'
        ],
        'zip' => [
             'required' => 'Enter zip code.',
             'min' => 'The zip code must be at least 5 characters.',
             'max' => 'The zip code may not be greater than 5 characters.',
             'regex' => "Only numbers are allowed in the zip code.",
        ],
        'name' => [
             'required' => 'Enter name.',
             'max' => 'The name may not be greater than 60 characters.',
             'regex' => "Only special characters - ' . and alphabets are allowed.",
        ],
        'organization' => [
            'max' => 'The organization name may not be greater than 100 characters.',
             'required' => 'Enter organization name.',
        ],
        'start_date' => [
             'required' => 'Choose Effective Start date.',
             'before' => 'Start date must be current/past date.'
        ],
        'end_date' => [
             'required' => 'Choose Effective End date.',
        ],
        'confirmation' => [
             'required' => 'Enter authorization confirmation number.',
              'max' => 'The Authorization Confirmation Number name may not be greater than 100 characters.',
        ],
        'doctor_name' => [
             'required' => 'Enter doctor name.',
             'regex' => "Only special characters - ' . and alphabets are allowed.",
              'max' => 'The Doctor Name name may not be greater than 60 characters.',
        ],
        'roles' => [
             'required' => 'Select role.',
             'exists' => 'Selected role does not exist, please choose different one.'
        ],
        'password' => [
             'required' => 'Enter password.',
             'confirmed' => 'Password and confirm password do not match.',
             'min'=> 'Enter password between 8 to 16 characters.',
             'max'=> 'Enter password between 8 to 16 characters.',
             'regex'=> 'Password should contain atleast an uppercase letter, a lowercase letter, a digit and a special character from ! $ # % @',
        ],
        'type'=>[
            'required' => 'Please select type.',
        ],
        'contact_phone' => [
            'phone' => 'Enter valid phone number.',
        ],
        'lives_with' => [
            'required' => "Select patient's living situation.",
            'exists' => 'Selected Lives With is not valid, please choose different one.',
        ],
        'password_confirmation' => [
            'required' => 'Confirm your password.',
        ],
        'contact_date' => [
            'required_if' => 'Enter contact date.',
            'before' => 'Contact date must be current/past date.',
        ],
        'contact_time' => [
            'required_if' => 'Enter contact time.',
            'before' => 'Contact time must be current/past time.',
        ],
        'location' => [
            'required_if' => 'Enter details.',
            'max' => 'Maximum 1000 characters are allowed.',
        ], 
        'assessment_date' => [
            'after' => 'Assessment date must be current/future date.'
        ],
        'assessment_time' => [
            'after' => 'Assessment time must be current/future time.'
        ],        
        'comment' => [
            'required' => 'Enter a comment.',
            'max' => 'Maximum 10000 characters are allowed.',
        ],
        'address' => [
            'required' =>'Enter address Line 1.',
            'max' => 'The address Line 1 may not be greater than 100 characters.',
        ],
        'address2' => [
            'max' => 'The Address Line 2 may not be greater than 100 characters.',
        ],
        'accept_or_refused_patient_decision' => [
            'required' =>'Patient Decision to Sign Consent Form is required.',
        ],
        'consent_form_signature_setup' => [
            'required' =>'Setup signature is required.',
        ],
        'acknowledge_receive_services' => [
            'required' =>'Acknowledge for consent to receive services is required.',
            'in' =>'Acknowledge for consent to receive services is required.',
        ],
        'acknowledge_emergency_medical_services' => [
            'required' =>'Acknowledge for emergency medical services is required.',
            'in' =>'Acknowledge for emergency medical services is required.',
        ],
        'acknowledge_release_medical_records' => [
            'required' =>'Acknowledge for Release of medical records is required.',
            'in' =>'Acknowledge for Release of medical records is required.',
        ],
        'acknowledge_release_vehicle' => [
            'required' =>'Acknowledge for Vehicle release is required.',
            'in' =>'Acknowledge for Vehicle release is required.',
        ],
        'acknowledge_signature' => [
            'required' =>'Acknowledgement on final signature is required.',
            'in' =>'Acknowledgement on final signature is required.',
        ],
        'acknowledge_patient_bill_of_rights' => [
            'required' =>'Acknowledgement on Statement of Patient Bill of Rights is required.',
            'in' =>'Acknowledgement on Statement of Patient Bill of Rights is required.',
        ],
        'consent_form_dpoa_name' => [
            'required' =>'DPOA Name is required.',
            'max' =>'DPOA name may not be greater than 50 characters.',
            'regex' => "Only special characters - ' . and alphabets are allowed.",
        ],
        'consent_form_living_will_executed' => [
            'required' =>'Living Will is required.',
        ],
        'consent_form_dpoa_executed' => [
            'required' =>'Durable Power of Attorney/Health Care is required.',
        ],
        'consent_form_signature_date' => [
            'required' =>'Signature date is required.',
        ],
        'consent_form_patient_initials' => [
            'required' =>'Patient’s/Client’s Initials is required.',
            'max' =>'Patient’s/Client’s Initials may not be greater than 10 characters.',
        ],
        'consent_form_documents_located_at_with' => [
            'required' =>'The documents are located at is required.',
        ],
        'consent_form_signature_date' => [
            'required' =>'Signature date is required.',
        ],
        'assigned_chw' => [
            'required' =>'Assign a community health worker.',
        ],
        'assigned_cm' => [
            'required' =>'Assign a case manager.',
        ],
        'assigned_md' => [
            'required' =>'Assign a medical director.',
        ],
        'consent_form_dpoa_phone_number' => [
            'required' =>'Phone Number is required.',
            'phone' =>'Enter valid phone number.',
        ],

        // validation for advanced directive tab
        'advance_healthcare_on_file' => [
            'required' =>'Advanced Health Care Directive On File is required.',
            'in' =>'Advanced Health Care Directive On File is invalid.',
        ],
        'advance_healthcare_checkboxes' => [
            'required' =>'Select healthcare directive.',
            'in' =>'Advanced Health Care Directive Checkbox is invalid.',
        ],
        'advance_healthcare_attorney_name' => [
            'required' =>'Enter payer name.',
            'max' =>'Payer name may not be greater than 50 characters.',
            'regex' => "Only special characters - ' . and alphabets are allowed.",
        ],
        'advance_healthcare_attorney_phone' => [
            'required' =>'Enter phone number.',
            'phone' =>'Enter valid phone number.',
        ],
        'advance_healthcare_attorney_relation' => [
            'required' =>'Choose relationship.',
        ],
        'polst_on_file' => [
            'required' =>'Set POLST.',
            'in' =>'POLST is invalid.',
        ],
        'polst_checkboxes' => [
            'required' =>'Select physician order for life-sustaining treatment.',
            'in' =>'Selected physician order for life-sustaining treatment is invalid.',
        ],
        'pcp_id' => [
            'exists' =>'PCF Information is invalid.',
            'required' =>'Choose PCP.',
        ],
        'hospice_provider_id' => [
            'exists' =>'Hospice Provider is invalid.',
        ],
        'home_health_provider_id' => [
            'exists' =>'Home Health Provider is invalid.',
        ],

        // Home Safety Tab
        'patient_functioning' => [
            'required' =>'Set patient functioning.',
            'in' =>'Patient Functioning is invalid.',
        ],
        'identifying_issues' => [
            'required' =>'Set identifying issues.',
            'in' =>'Identifying Issues is invalid.',
        ],
        'durable_medical_equipment' => [
            'in' =>'Durable Medical Equipment is invalid.',
        ],
        'durable_medical_equipment_other_text' => [
            'required' =>'Please enter value.',
            'max' =>'Maximum 100 characters are allowed.',
        ],
        'identifying_issues_other_text' => [
            'required' =>'Please enter value.',
            'max' =>'Maximum 100 characters are allowed.',
        ],
        'patient_functioning_text' => [
            'max' =>'Maximum 1000 characters are allowed.',
        ],
        'durable_medical_equipment_text' => [
            'max' =>'Maximum 1000 characters are allowed.',
        ],
        'reason' => [
            'required' => 'Enter reason for rejection.',
            'max' => 'Maximum 1000 characters are allowed.',
        ],


        //  MD Medical tab
        'frequency' => [
            'required' =>'Add frequency.',
            'max' =>'Maximum 100 characters are allowed.',
        ],
        'dosage' => [
            'required' =>'Enter dosage.',
            'integer' =>'Enter numbers only.',
            'digits_between' =>'The dosage may not be greater than 5 number.',
            'min' =>'Only postive values are allowed.',
        ],
        'units' => [
            'required' =>'Select units.',
            'in' =>'Selected unit is invalid.',
        ],
        'substance_abuse' => [
            'required' =>'Set substance abuse.',
            'in' =>'Selected substance abuse is invalid.',
        ],
        'icd_code' => [
            'required' =>'Add medical diagnosis.',
            'exists' => 'Selected medical diagnosis is not valid, please choose different one.',
        ],
        'ed_visits_last_12_months' => [
            'required' =>'Enter number of ED visits.',
            'integer' =>'Enter numbers only.',
            'digits_between' =>'Three numbers are allowed at max.',
            'min' =>'Only postive values are allowed.',
        ],
        'ed_admissions_last_12_months' => [
            'required' =>'Enter number of admissions.',
            'integer' =>'Enter numbers only.',
            'digits_between' =>'Three numbers are allowed at max.',
            'min' =>'Only postive values are allowed.',
        ],
        'otp' => [
            'required' =>'Enter OTP.'
        ],

        'description' => [
            'max' => 'The title name may not be greater than 1000 characters.',
            'required' => 'Enter description.',
            'regex' => "Only numbers, special characters - ' . and alphabets are allowed.",
        ],
        'type' => [
             'required' => 'Please select type.',
             'in' =>'Selected type is invalid.',
        ],
        'title' => [
            'max' => 'The title may not be greater than 100 characters.',
            'required' => 'Enter title.',
            'regex' => "Only numbers, special characters - ' . and alphabets are allowed.",
        ],
        'location' => [
            'max' => 'The view/location may not be greater than 1000 characters.',
             'required' => 'Enter view/location.',
        ],
        'metric_id' => [
             'required' => 'Please select metric.',
             'in' =>'Selected type is invalid.',
        ],

        'assigned_roles' => [
             'required' => 'Please select role.',
             'exists' =>'Selected role is invalid.',
        ],
        'file_path' => [
            'required' => 'Choose a file.',
            'mimes' => 'Document must be a PDF.',
            'max' => 'File size may not be greater than 10 MB.',
        ],
        'enrollment_status' => [
             'required' => 'Please select enrollment status.',
             'in' =>'Selected status is invalid.',
        ],
        'allergy_type'=>[
            'required' => 'Please select allergy type.',
        ],
        'priority' => [
            'required' => 'Set diagnosis priority.',
            'integer' => 'Only numeric value allowed.',
            'max' => 'Only three number are allowed.'
        ],
        'notes' => [
            'required' => 'Please enter notes.',
            'max' => 'Notes may not be greater than 10000 characters.',
        ],
        'goal_ids' => [
            'required' => 'Please select atleast one goal.'
        ],

        'hippa_form_signature_setup' => [
            'required' =>'Setup signature is required.',
        ],
        'acknowledge_authorize_person' => [
            'required' =>'Acknowledge for Authorized persons to use and disclose protected health information is required',
            'in' =>'Acknowledge for Authorized persons to use and disclose protected health information is required.',
        ],
        'acknowledge_description_of_info' => [
            'required' =>'Acknowledge for Description of information to be disclosed is required.',
            'in' =>'Acknowledge for Description of information to be disclosed is required.',
        ],
        'acknowledge_purpose_to_use' => [
            'required' =>'Acknowledge for Purpose of the use or disclosure is required.',
            'in' =>'Acknowledge for Purpose of the use or disclosure is required.',
        ],
        'acknowledge_validity_of_form' => [
            'required' =>'Acknowledge for Validity of authorization form is required.',
            'in' =>'Acknowledge for Validity of authorization form is required.',
        ],
        'acknowledge_signature' => [
            'required' =>'Acknowledgement on final signature is required.',
            'in' =>'Acknowledgement on final signature is required.',
        ],
        'purpose' => [
            'required' => 'Select purpose.'
        ],
        'via' => [
            'required' => 'Select via.'
        ],
        'barrier_id' => [
            'required' => 'Select barrier.'
        ],

        'visit_content' => [
            'required' =>'Add atleast one content.',
        ],
        'other_notes' => [
            'required' =>'Enter notes.',
        ],
        'action' => [
            'required' =>'Select action.',
        ],
        'summary' => [
            'required' => 'Enter notes.',
            'max' => 'Maximum 1000 characters are allowed.'
        ],
        'assigned_users' => [
            'required' =>'Select user(s) to notify.',
        ],
        'flag' => [
            'required' =>'Raise flag.',
        ],
        'follow_up_item' => [
            'required' =>'Enter follow up item.',
            'max' => 'Maximum 200 characters are allowed.',

        ],
        'follow_up_date' => [
            'required' =>'Enter follow up date.',
        ],

        'progress_notes' => [
            'required' => 'Enter notes.',
            'max' => 'Maximum 10,000 characters are allowed.'
        ],

        'weight' => [
            'required' =>'Enter Weight.',
            'min' => 'The weight be at least 1 numbers.',
            'max' => 'The weight may not be greater than 3 numbers.',
            'numeric' => 'Only numeric value allowed.',
        ],
        'a1c' => [
            'required' =>'Enter A1C.',
            'min' => 'The a1c be at least 1 numbers.',
            'max' => 'The a1c may not be greater than 3 numbers.',
            'numeric' => 'Only numeric value allowed.',
        ],
        'blood_pressure_high' => [
            'required' =>'Enter systolic blood pressure.',
            'min' => 'The systolic blood pressure be at least 1 numbers.',
            'max' => 'The systolic blood pressure may not be greater than 3 numbers.',
            'numeric' => 'Only numeric value allowed.',
        ],
        'blood_pressure_low' => [
            'required' =>'Enter diastolic blood pressure.',
            'min' => 'The diastolic blood pressure be at least 1 numbers.',
            'max' => 'The diastolic blood pressure may not be greater than 3 numbers.',
            'numeric' => 'Only numeric value allowed.',
        ],
        'pulse' => [
            'required' =>'Enter pulse rate.',
            'min' => 'The pulse rate be at least 1 numbers.',
            'max' => 'The pulse rate may not be greater than 3 numbers.',
            'numeric' => 'Only numeric value allowed.',
        ],


    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [],
    'diagnosis_not_assigned' =>'Please add atleast one diagnosis to save the care plan.',
];
