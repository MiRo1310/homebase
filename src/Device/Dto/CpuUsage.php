<?php

namespace App\Device\Dto;

readonly class CpuUsage
{
    /**
     * @param int   $timeStartNs    Zeit der ersten Messung auf dem Gerät (date +%s%N, Nanosekunden)
     * @param int   $timeEndNs      Zeit der zweiten Messung
     * @param int   $usageStartUsec CPU-Zähler der ersten Messung (usage_usec, Mikrosekunden seit Containerstart)
     * @param int   $usageEndUsec   CPU-Zähler der zweiten Messung
     * @param float $cores          effektive Kerne (Kontingent ÷ Periode aus cpu.max, ohne Limit nproc)
     */
    public function __construct(
        public int   $timeStartNs,
        public int   $timeEndNs,
        public int   $usageStartUsec,
        public int   $usageEndUsec,
        public float $cores,
    )
    {
    }

    /**
     * Auslastung zwischen den beiden Messungen in Prozent (0–100).
     */
    public function getUsagePercent(): float
    {
        $elapsedUsec = ($this->timeEndNs - $this->timeStartNs) / 1000;

        if ($elapsedUsec <= 0 || $this->cores <= 0) {
            return 0.0;
        }

        $usedUsec = $this->usageEndUsec - $this->usageStartUsec;

        return $usedUsec / ($elapsedUsec * $this->cores) * 100;
    }
}
