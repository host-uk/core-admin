<admin:module title="Activity log" subtitle="View recent activity in your workspace">
    <admin:filter-bar cols="4">
        <admin:search model="search" placeholder="Search activities..." />
        @if(count($this->logNames) > 0)
            <admin:filter model="logName" :options="$this->logNameOptions" />
        @endif
        @if(count($this->events) > 0)
            <admin:filter model="event" :options="$this->eventOptions" />
        @endif
        <admin:clear-filters :show="$search || $logName || $event" />
    </admin:filter-bar>

    <admin:activity-log
        :items="$this->activityItems"
        :pagination="$this->activities"
        empty="No activity recorded yet."
        emptyIcon="clock"
    />
</admin:module>
