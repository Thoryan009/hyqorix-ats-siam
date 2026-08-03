export default {
  job: {
    module: 'Job',
    management: 'Job Management',
    performance: 'Job Performance',
    job_overview: 'Job Overview',
    system_information: 'System Information',

    add: 'Add Job',
    edit: 'Edit Job',
    view: 'View Job Details',
    delete: 'Delete Job',


    job_id: 'Job ID',
    job_information: 'Job Information',
    job_name: 'Job Name',
    price_per_candidate_taka: 'Price Per Candidate (Taka)',
    client_commission_per_candidate: 'Client Commission Per Candidate (Taka)',

    min_age: 'Min Age',
    max_age: 'Max Age',
    contract_length: 'Contract Length',
    language: 'Language',
    qualification: 'Qualification',
    application_deadline: 'Application Deadline',
    description: 'Description',

    upload_image: 'Upload Job Image (Optional)',
    placeholder: 'Eg: John Doe',

    information: 'Job Information',
    total_jobs: 'Total Jobs',
    jobs: 'Jobs',

    delete_confirmation:
      'Are you sure you want to delete this Job?',
  },
  ats:{
    ats: 'Ats Page',
    ats_applications: 'ATS Applications',
    track: 'Track',
    process_stage: 'Process Stage',
    application_process_status: 'Application Process Status',
    select_job_to_preview: 'Select a job to preview details.',
    no_applicants_found: 'No applicants found.',
    search_by_name_or_passport: 'Search by name or passport no',
    search_by_client_name: 'Search by client name',
    applicant_processes: 'Applicant Processes',
    select_applicant_to_manage: 'Select an applicant to manage their process',
    applicant_documents: 'Applicant Documents',
    no_documents_available: 'No documents available',

    applicant_has_been_rejected: 'The Selected applicant has been rejected.',
    select_a_process_to_manage: 'Select a process to manage',
    view_documents: 'View Documents',
    delete_process: 'Delete Process',

     process: {
      offer_extended: 'offer_extended',
      visa_authorization: 'visa_authorization',
      medical_test: 'medical_test',
      police_clearance: 'police_clearance',
      trade_test: 'trade_test',
      biometric_enrollm: 'biometric_enrollm',
      embassy_submission: 'embassy_submission',
      bmet_training: 'bmet_training',
      bmet_biometric_enrollm: 'bmet_biometric_enrollm',
      immigration_clearance: 'immigration_clearance',
      pta_request: 'pta_request',
      tra_process: 'tra_process',
      on_boarding: 'on_boarding',
  },

  //process input fields
  select_next_process: 'Select Next Process',
  next_process: 'Next Process',
  not_started_yet: 'Not started yet',
  enter_remarks_here: 'Enter your remarks here...',
  process_status: 'Process Status',
  remarks: 'Remarks',

  process_1: 'Process 1: Offer Extended Form',
  process_2: 'Process 2: Visa / Work Permit Authorization Form',
  process_3: 'Process 3: Medical Test Form',
  process_4: 'Process 4: Police Clearance Form',
  process_5: 'Process 5: Trade / Takamol Test Form',
  process_6: 'Process 6: Biometric / Tasheer Enrollment Form',
  process_7: 'Process 7: Embassy Submission Form',
  process_8: 'Process 8: BMET Training Form',
  process_9: 'Process 9: BMET Biometric Enrollment Form',
  process_10: 'Process 10: Immigration Clearance Form',
  process_11: 'Process 11: PTA Request Form',
  process_12: 'Process 12: TRA Process Form',
  process_13: 'Process 13: On Boarding Form',

// process 1
  offer_status: 'Offer Status',

// process 2
  visa_number: 'Visa Number',
  date_of_issue: 'Date of Issue',
  visa_profession: 'Visa Profession',
  enter_you_visa_profession_info: 'Enter you visa profession info...',

// process 3
  date_of_medical: 'Date of Medical',
  medical_center_name: 'Medical Center Name',
  medical_result: 'Medical Result',
  enter_medical_center_name: 'Enter medical center name...',
  unfit_reason: 'Unfit Reason',
  enter_unfit_reason: 'Enter reason for unfit status...',

  //process 4
  date_of_apply: 'Date of Apply',
  police_station_name: 'Police Station Name',
  enter_police_station_name: 'Enter police station name...',
  not_issued_reason: 'Not Issued Reason',
  enter_not_issued_reason: 'Enter reason if police clearance is not issued...',

  //process 5
  trade_test_date: 'Trade Test Date',
  name_of_the_center: 'Name of the Center',
  enter_test_center_name: 'Enter test center name...',
  trade_test_result: 'Trade Test Result',
  enter_trade_test_result: 'Enter trade test result...',

  //process 6
  mofa_status: 'Mofa Status',
  date_of_enrollment: 'Date of Enrollment',
  enter_date_of_enrollment: 'Enter date of enrollment...',
  biometric_status: 'Biometric Status',

  //process 7
  date_of_submission: 'Date of Submission',
  endorsement_date: 'Endorsement Date',
  visa_expiry_date: 'Visa Expiry Date',
  collection_date: 'Collection Date',

  //process 8
  training_start_date: 'Training Start Date',
  finished_date: 'Finished Date',
  enter_finished_date: 'Enter finished date (optional)',

  // process 10
  immigration_clearance_status: 'Immigration Clearance Status',

  // process 11
  pta_request_date: 'Pta Request Date',

  // process 12
  ticket_no: 'Ticket No',
  enter_ticket_number: 'Enter ticket number...',
  flight_number_1: 'Flight Number 1',
  enter_flight_number_1: 'Enter flight number 1...',
  flight_number_2: 'Flight Number 2',
  enter_flight_number_2: 'Enter flight number 2...',
  departure_date: 'Departure Date',
  flight_from_city: 'Flight From (City)',
  select_city: 'Select city...',
  departure_time: 'Departure Time',
  enter_departure_time: 'Enter departure time...',
  airline: 'Airline',
  enter_airline_name: 'Enter airline name...',
  final_destination: 'Final Destination',
  enter_final_destination: 'Enter final destination...',
  landing_date: 'Landing Date',
  landing_time_local: 'Landing Time (Local)',
  enter_landing_time: 'Enter landing time ...',
  ticket_passport: 'Ticket Passport ',
  select_status: 'Select Status',
  print_acknowledgement: 'Print Acknowledgement',

  // process 13
  select_flight_status: 'Select Flight Status',
},

job_price:{
  title: 'Job Price Management',
  add: 'Add Job List Details',
  view: 'View Job List Details',
  edit: 'Edit Job List Details',

  fee_name: 'Fee Name',
  amount: 'Amount',
  amount_usd: 'Amount USD',

  fee_head_management: 'Fee Head Management',
  add_fee_head: 'Add Fee Head',
  view_fee_head: 'View Fee Head Details',
  edit_fee_head: 'Edit Fee Head',

  fee_category: 'Fee Category',
  price_head_name: 'Price Head Name',
  price_category: 'Price Category',
  head_category: 'Head Category',
  category_name: 'Category Name',

  price_details: 'Price Details',
  category: 'Category',
  fee_category_management: 'Fee Category Management',
  add_fee_category: 'Add Fee Category',
  view_fee_category_details: 'View Fee Category Details',
  edit_fee_category: 'Edit Fee Category',



}
}
