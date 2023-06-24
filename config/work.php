<?php

class Work
{
    private int $id;
    private int $member_id;
    private float $temperature;
    private string $device_ip;
    private string $device_key;
    private string $day_name;
    private string $scan_date;
    private string $scan_time;
    private string $scan_timestamp;
    private string $timestamp;
    private string $work;
    private string $sql;

    public function __construct(int    $member_id, float $temperature, string $device_ip,
                                string $device_key, string $day_name, string $scan_date, string $scan_time,
                                string $scan_timestamp, string $timestamp, string $work, ?int $id = 0)
    {
        $this->id = $id;
        $this->member_id = $member_id;
        $this->temperature = $temperature;
        $this->device_ip = $device_ip;
        $this->device_key = $device_key;
        $this->day_name = $day_name;
        $this->scan_date = $scan_date;
        $this->scan_time = $scan_time;
        $this->scan_timestamp = $scan_timestamp;
        $this->timestamp = $timestamp;
        $this->work = $work;
    }

    public function start_work_scan(): void
    {
        $this->sql = "INSERT INTO faceid (F_member_id, F_date_name, F_date, F_time_in, F_temperature_in, F_status_in,
                F_cr_date_in, F_timestamp_by_device_in, F_device_ip_in, F_device_key_in) 
                VALUES ('$this->member_id', '$this->day_name', '$this->scan_date', '$this->scan_time', 
                '$this->temperature', '$this->work', '$this->scan_timestamp', '$this->timestamp', '$this->device_ip', 
                '$this->device_key')";
    }

    public function fix_start_work_scan(): void
    {
        $this->sql = "UPDATE faceid SET F_time_in = '$this->scan_time', F_temperature_in = '$this->temperature', 
                        F_status_in = '$this->work', F_cr_date_in = '$this->scan_timestamp', 
                        F_timestamp_by_device_in = '$this->timestamp', F_device_ip_in = '$this->device_ip', 
                        F_device_key_in = '$this->device_key' WHERE F_id = '$this->id'";
    }

    public function end_work_scan(): void
    {
        $this->sql = "UPDATE faceid SET F_time_out = '$this->scan_time', F_temperature_out = '$this->temperature', 
                        F_status_out = '$this->work', F_cr_date_out = '$this->scan_timestamp', 
                        F_timestamp_by_device_out = '$this->timestamp', F_device_ip_out = '$this->device_ip', 
                        F_device_key_out = '$this->device_key' WHERE F_id = '$this->id'";
    }

    public function getterSQL(): string
    {
        return $this->sql;
    }

}