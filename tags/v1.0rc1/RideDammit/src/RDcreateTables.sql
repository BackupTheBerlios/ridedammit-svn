# MySQL dump 8.12
#
# Host: localhost    Database: ridesDB
#--------------------------------------------------------
# Server version	3.23.33

#
# Table structure for table 'bikes'
#

CREATE TABLE rides_bikes (
  bikeID int(11) NOT NULL auto_increment,
  bike varchar(100) NOT NULL default '',
  riderID int(11) NOT NULL default '0',
  PRIMARY KEY (bikeID)
) TYPE=MyISAM;

#
# Table structure for table 'locations'
#

CREATE TABLE rides_locations (
  locationID int(11) NOT NULL auto_increment,
  location varchar(100) NOT NULL default '',
  description text NOT NULL,
  type smallint NOT NULL default 0,
  PRIMARY KEY (locationID)
) TYPE=MyISAM;

#
# Table structure for table 'riders'
#

CREATE TABLE rides_riders (
  riderID int(11) NOT NULL auto_increment,
  firstName varchar(100) NOT NULL default '',
  lastName varchar(100) NOT NULL default '',
  pword varchar(17) NOT NULL default '',
  PRIMARY KEY (riderID)
) TYPE=MyISAM;

#
# Table structure for table 'rides'
#

CREATE TABLE rides_rides (
  rideID int(11) NOT NULL auto_increment,
  riderID int(11) NOT NULL default '0',
  date datetime NOT NULL default '0000-00-00 00:00:00',
  distance double NOT NULL default '0',
  maxSpeed double NOT NULL default '0',
  time time NOT NULL default '00:00:00',
  locationID int(11) NOT NULL default '0',
  temperature double NOT NULL default '0',
  wind enum('Still','Mild','Medium','Breezy','Gusty') NOT NULL default 'Still',
  sky enum('Clear','Some','Partly','Mostly','Cloudy') NOT NULL default 'Clear',
  effortLevel enum('Low','Mild','Medium','Hard','Racing') NOT NULL default 'Low',
  bikeID int(11) NOT NULL default '0',
  notes text NOT NULL,
  PRIMARY KEY (rideID),
  KEY bikeID(bikeID),
  KEY riderID(riderID),
  KEY locationID(locationID)
) TYPE=MyISAM;

