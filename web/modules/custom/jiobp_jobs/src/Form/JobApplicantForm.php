<?php

namespace Drupal\jiobp_jobs\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\file\Entity\File;

class JobApplicantForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'job_applicant_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Job Posting Date
    $form['field_posted_on'] = [
      '#type' => 'date',
      '#title' => $this->t('Job Posting Date'),
      '#required' => TRUE,
    ];

    // Other form fields like 'full_name', 'contact_number', etc.
    $form['full_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Full Name'),
      '#required' => TRUE,
    ];

    $form['contact_number'] = [
      '#type' => 'tel',
      '#title' => $this->t('Contact Number'),
      '#required' => TRUE,
    ];

    $form['email_address'] = [
      '#type' => 'email',
      '#title' => $this->t('Email Address'),
      '#required' => TRUE,
    ];

    // Submit button
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the job posting date
    $posted_on = $form_state->getValue('field_posted_on');
    
    if (!empty($posted_on)) {
      // Convert the date to a DrupalDateTime object
      $posted_on_date = DrupalDateTime::createFromFormat('Y-m-d', $posted_on);
      $posted_on_date->setTime(0, 0); // Set the time to 00:00 to ensure it's compared accurately
      
      // Get the current date
      $current_date = new DrupalDateTime();
      $current_date->setTime(0, 0); // Set the time to 00:00 for accurate comparison
      
      // Calculate the difference in days between the current date and the job posting date
      $diff = $current_date->diff($posted_on_date)->days;
      
      // If the job posting date is more than 15 days ago, set an error
      if ($diff > 15) {
        $form_state->setErrorByName(
          'field_posted_on',
          $this->t('The job posting is older than 15 days and is no longer valid.')
        );
      }
    }
  }
  

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get submitted values
    $full_name = $form_state->getValue('full_name');
    $contact_number = $form_state->getValue('contact_number');
    $email_address = $form_state->getValue('email_address');
    $dob = $form_state->getValue('dob');

    // Process the uploaded files (e.g., resume, video resume)
    $resume_file = $form_state->getValue('upload_resume');
    $video_resume_file = $form_state->getValue('video_resume');

    // Handle file uploads
    $resume_file_saved = $this->handleFileUpload($resume_file);
    $video_resume_file_saved = $this->handleFileUpload($video_resume_file);

    // Check if files were uploaded successfully
    if ($resume_file_saved) {
      \Drupal::logger('jiobp_jobs')->debug('Resume uploaded successfully: ' . $resume_file_saved->getFilename());
    } else {
      \Drupal::logger('jiobp_jobs')->error('Error uploading resume.');
    }

    if ($video_resume_file_saved) {
      \Drupal::logger('jiobp_jobs')->debug('Video Resume uploaded successfully: ' . $video_resume_file_saved->getFilename());
    } else {
      \Drupal::logger('jiobp_jobs')->error('Error uploading video resume.');
    }

    // Log form data for debugging
    \Drupal::logger('jiobp_jobs')->debug('Form submitted with data:');
    \Drupal::logger('jiobp_jobs')->debug('Full Name: ' . $full_name);
    \Drupal::logger('jiobp_jobs')->debug('Contact Number: ' . $contact_number);
    \Drupal::logger('jiobp_jobs')->debug('Email Address: ' . $email_address);
    \Drupal::logger('jiobp_jobs')->debug('Date of Birth: ' . $dob);

    // Success message
    \Drupal::messenger()->addMessage($this->t('Your resume has been successfully submitted.'));
  }

  /**
   * Handle file uploads (Resume, Video Resume).
   */
  private function handleFileUpload($file) {
    if (!empty($file)) {
      // File validation could be added here (check file types and sizes).
      $file_data = file_save_data(file_get_contents($file[0]['temporary']), 'public://uploads/' . $file[0]['filename']);
      
      if ($file_data) {
        // Return the saved file entity
        return $file_data;
      }
      else {
        // Log an error if the file cannot be saved
        \Drupal::logger('jiobp_jobs')->error('Failed to save file: ' . $file[0]['filename']);
      }
    }
    return NULL;
  }
}
