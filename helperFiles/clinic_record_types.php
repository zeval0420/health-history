<?php
/*
 * Config for every per-teacher record type. The records endpoint, the teacher page, the print
 * summary, the reports and the autocomplete whitelist all read this file.
 * To add a record type: one migration (table with id, teacher_id, <fields>, notes, created_by,
 * created_at, updated_at, deleted_at) + one entry here. No new page needed.
 *
 * Field keys: name, label, type (text|date), lookup (autocomplete category), required, max (text length)
 * `notes` (textarea) is added to every type automatically. `order` is the column the list sorts by (newest first).
 */
return [
    'medications' => [
        'table' => 'clinic_medications', 'label' => 'Medications', 'order' => 'start_date',
        'fields' => [
            ['name' => 'name', 'label' => 'Medication', 'type' => 'text', 'lookup' => 'medication', 'required' => true],
            ['name' => 'dosage', 'label' => 'Dosage', 'type' => 'text', 'lookup' => 'dosage', 'max' => 100],
            ['name' => 'frequency', 'label' => 'Frequency', 'type' => 'text', 'lookup' => 'frequency', 'max' => 100],
            ['name' => 'reason', 'label' => 'Reason', 'type' => 'text', 'lookup' => 'med_reason'],
            ['name' => 'start_date', 'label' => 'Started', 'type' => 'date'],
            ['name' => 'end_date', 'label' => 'Stopped', 'type' => 'date'],
            ['name' => 'prescriber', 'label' => 'Prescriber', 'type' => 'text', 'lookup' => 'prescriber', 'max' => 150],
        ],
    ],
    'conditions' => [
        'table' => 'clinic_conditions', 'label' => 'Conditions / Diseases', 'order' => 'diagnosed_on',
        'fields' => [
            ['name' => 'name', 'label' => 'Condition', 'type' => 'text', 'lookup' => 'condition', 'required' => true],
            ['name' => 'status', 'label' => 'Status', 'type' => 'text', 'lookup' => 'condition_status', 'max' => 100],
            ['name' => 'diagnosed_on', 'label' => 'Diagnosed', 'type' => 'date'],
            ['name' => 'resolved_on', 'label' => 'Resolved', 'type' => 'date'],
        ],
    ],
    'vaccinations' => [
        'table' => 'clinic_vaccinations', 'label' => 'Vaccinations', 'order' => 'date_given',
        'fields' => [
            ['name' => 'vaccine', 'label' => 'Vaccine', 'type' => 'text', 'lookup' => 'vaccine', 'required' => true],
            ['name' => 'dose_label', 'label' => 'Dose', 'type' => 'text', 'lookup' => 'dose_label', 'max' => 50],
            ['name' => 'date_given', 'label' => 'Date given', 'type' => 'date'],
            ['name' => 'next_due', 'label' => 'Next due', 'type' => 'date'],
            ['name' => 'lot_no', 'label' => 'Lot no.', 'type' => 'text', 'max' => 100],
            ['name' => 'given_by', 'label' => 'Given by', 'type' => 'text', 'lookup' => 'given_by', 'max' => 150],
        ],
    ],
    'allergies' => [
        'table' => 'clinic_allergies', 'label' => 'Allergies', 'order' => 'id',
        'fields' => [
            ['name' => 'allergen', 'label' => 'Allergen', 'type' => 'text', 'lookup' => 'allergen', 'required' => true],
            ['name' => 'reaction', 'label' => 'Reaction', 'type' => 'text', 'lookup' => 'reaction'],
            ['name' => 'severity', 'label' => 'Severity', 'type' => 'text', 'lookup' => 'severity', 'max' => 50],
        ],
    ],
    'visits' => [
        'table' => 'clinic_visits', 'label' => 'Clinic visits', 'order' => 'visit_date',
        'fields' => [
            ['name' => 'visit_date', 'label' => 'Date', 'type' => 'date', 'required' => true],
            ['name' => 'complaint', 'label' => 'Complaint', 'type' => 'text', 'lookup' => 'complaint'],
            ['name' => 'assessment', 'label' => 'Assessment', 'type' => 'text', 'lookup' => 'assessment'],
            ['name' => 'action_taken', 'label' => 'Action taken', 'type' => 'text', 'lookup' => 'action_taken'],
            ['name' => 'attended_by', 'label' => 'Attended by', 'type' => 'text', 'lookup' => 'attended_by', 'max' => 150],
        ],
    ],
    'procedures' => [
        'table' => 'clinic_procedures', 'label' => 'Procedures / Hospitalizations', 'order' => 'procedure_date',
        'fields' => [
            ['name' => 'name', 'label' => 'Procedure', 'type' => 'text', 'lookup' => 'procedure', 'required' => true],
            ['name' => 'procedure_date', 'label' => 'Date', 'type' => 'date'],
            ['name' => 'facility', 'label' => 'Facility', 'type' => 'text', 'lookup' => 'facility', 'max' => 150],
        ],
    ],
    'other' => [
        'table' => 'clinic_other_records', 'label' => 'Other', 'order' => 'record_date',
        'fields' => [
            ['name' => 'type', 'label' => 'Type', 'type' => 'text', 'lookup' => 'other_type', 'required' => true, 'max' => 100],
            ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'required' => true],
            ['name' => 'record_date', 'label' => 'Date', 'type' => 'date'],
        ],
    ],
];
