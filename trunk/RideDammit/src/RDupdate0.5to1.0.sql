

alter table bikes rename to rides_bikes;
alter table locations rename to rides_locations;
alter table rides rename to rides_rides;
alter table riders rename to rides_riders;

alter table rides_locations add column tc smallint not null;
update rides_locations set tc=1 where type="Trail";
update rides_locations set tc=2 where type="Offroad";
update rides_locations set tc=3 where type="Mixed";
alter table rides_locations drop column type;
alter table rides_locations change column tc type smallint not null;