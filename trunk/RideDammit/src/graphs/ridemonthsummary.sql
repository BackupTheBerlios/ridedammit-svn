# Connection: ridesDB
# Host: localhost
# Saved: 2003-03-26 11:50:16
# 
SELECT rides_rides.riderID,
   firstName, lastName,
    sum(distance) as tdistance, sum(time_to_sec(time)/3600) as ttime, 
   sum(if(rides_locations.type=0,distance,0)) as dist0,
   sum(if(rides_locations.type=1,distance,0)) as dist1,
   sum(if(rides_locations.type=2,distance,0)) as dist2,
   sum(if(rides_locations.type=3,distance,0)) as dist3,
   sum(if(rides_locations.type=0,time_to_sec(time)/3600,0)) as time0,
   sum(if(rides_locations.type=1,time_to_sec(time)/3600,0)) as time1,
   sum(if(rides_locations.type=2,time_to_sec(time)/3600,0)) as time2,
   sum(if(rides_locations.type=3,time_to_sec(time)/3600,0)) as time3,
   date_format(date, "%Y-%m") as month,
   date_format(date, "%b %y") as hmonth
FROM `rides_rides` inner join rides_locations on rides_rides.locationID=rides_locations.locationID
  inner join rides_riders on rides_rides.riderID=rides_riders.riderID
where date > date_sub(date_add(date_format(now(), "%Y-%m-01"), interval 1 month), interval 1 year)
group by month, riderID
order by month
