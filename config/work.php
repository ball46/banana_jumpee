<?php

class Work
{
    private int $id;
    private int $member_id;
    private float $temperature;
    private int $in_out;
    private string $device_ip;
    private string $device_key;
    private string $day_name;
    private string $scan_date;
    private string $scan_time;
    private string $scan_timestamp;
    private string $timestamp;
    private string $work;
    private string $sql;

    public function __construct(int $member_id, float $temperature, int $in_out, string $device_ip,
                                string $device_key, string $day_name, string $scan_date, string $scan_time,
                                string $scan_timestamp, string $timestamp, string $work, ?int $id = null)
    {
        $this->id = $id;
        $this->member_id = $member_id;
        $this->temperature = $temperature;
        $this->in_out = $in_out;
        $this->device_ip = $device_ip;
        $this->device_key = $device_key;
        $this->day_name = $day_name;
        $this->scan_date = $scan_date;
        $this->scan_time = $scan_time;
        $this->scan_timestamp = $scan_timestamp;
        $this->timestamp = $timestamp;
        $this->work = $work;
    }

    public function first_scan(): void
    {
        $this->sql = "INSERT INTO faceid (F_member_id, F_temperature, F_in_out, F_device_ip,F_device_key, F_date_name, 
                F_date, F_time, F_cr_date, F_timestamp_by_device, F_work) 
                VALUES ('$this->member_id', '$this->temperature', '$this->in_out', '$this->device_ip', '$this->device_key',
                '$this->day_name','$this->scan_date', '$this->scan_time', '$this->scan_timestamp', '$this->timestamp', 
                '$this->work')";
    }

    public function scan_again(): void
    {
        $this->sql = "UPDATE faceid SET F_temperature = '$this->temperature', F_in_out = '$this->in_out', 
                    F_device_ip = '$this->device_ip', F_device_key = '$this->device_key', F_time = '$this->scan_time',
                    F_cr_date = '$this->scan_timestamp', F_timestamp_by_device = '$this->timestamp', F_work = '$this->work' 
                    WHERE F_id = '$this->id'";
    }

    public function getterSQL(): string
    {
        return $this->sql;
    }

}