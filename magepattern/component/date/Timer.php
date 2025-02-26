<?php
namespace Magepattern\Component\Date;

class Timer
{
    /**
     * @var int|float
     */
    protected
        $currentStep = 0,
        $start = 0,
        $pause = 0,
        $end = 0,
        $totalDuration = 0;

    /**
     * @var array
     */
    protected array $timeSteps = [];

    /**
     * @return int|float
     */
    private function timeSnapshot(): int|float
    {
        return (microtime(true) - time()) * 1000;
    }

    /**
     * Reset completely the timer
     */
    public function reset()
    {
        $this->currentStep = 0;
        $this->start = 0;
        $this->pause = 0;
        $this->end = 0;
        $this->totalDuration = 0;
        $this->timeSteps = [];
    }

    /**
     * Start timer
     * @return Timer
     */
    public function start()
    {
        $this->start = $this->timeSnapshot();
        $this->currentStep = 0;
        $this->timeSteps[0] = [
            'start' => $this->start,
            'end' => 0,
            'duration' => 0
        ];
        return $this;
    }

    /**
     * Register a duration - if the timer was pause, it starts again
     * @param bool $stop
     */
    public function click(bool $stop = false)
    {
        $end = $this->timeSnapshot();
        $duration = $end - ($this->pause ? $end - $this->pause : 0) - $this->timeSteps[$this->currentStep]['start'];
        if($this->pause) $this->pause = 0;
        $this->timeSteps[$this->currentStep]['end'] = $end;
        $this->timeSteps[$this->currentStep]['duration'] = $duration;
        $this->totalDuration += $duration;

        if(!$stop) {
            $this->currentStep = $this->currentStep + 1;
            $this->timeSteps[$this->currentStep] = [
                'start' => $end,
                'end' => 0,
                'duration' => 0
            ];
        }
    }

    /**
     * Pause the timer
     */
    public function pause()
    {
        $this->pause = $this->timeSnapshot();
    }

    /**
     * Continue the timer
     */
    public function continue()
    {
        if($this->pause) $this->pause = $this->timeSnapshot() - $this->pause;
    }

    /**
     * End the timer
     * @return array
     */
    public function stop(): array
    {
        $this->click(true);
        $this->end = $this->timeSteps[$this->currentStep]['end'];
        $timer = [
            'start' => $this->start,
            'end' => $this->end,
            'total' => $this->totalDuration,
            'steps' => $this->timeSteps
        ];
        $this->reset();
        return $timer;
    }
}