/* ===========================================================
 * JFreeChart : a free chart library for the Java(tm) platform
 * ===========================================================
 *
 * (C) Copyright 2000-2004, by Object Refinery Limited and Contributors.
 *
 * Project Info:  http://www.jfree.org/jfreechart/index.html
 *
 * This library is free software; you can redistribute it and/or modify it under the terms
 * of the GNU Lesser General Public License as published by the Free Software Foundation;
 * either version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along with this
 * library; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330,
 * Boston, MA 02111-1307, USA.
 *
 * [Java is a trademark or registered trademark of Sun Microsystems, Inc. 
 * in the United States and other countries.]
 *
 * -------------------------
 * StackedBarChartDemo4.java
 * -------------------------
 * (C) Copyright 2004, by Object Refinery Limited and Contributors.
 *
 * Original Author:  David Gilbert (for Object Refinery Limited);
 * Contributor(s):   -;
 *
 * $Id: StackedBarChartDemo4.java,v 1.6 2004/05/12 16:01:58 mungady Exp $
 *
 * Changes
 * -------
 * 29-Apr-2004 : Version 1 (DG);
 *
 */


import java.awt.Color;
import java.awt.GradientPaint;
import java.awt.Paint;

import org.jfree.chart.ChartFactory;
import org.jfree.chart.ChartPanel;
import org.jfree.chart.JFreeChart;
import org.jfree.chart.LegendItem;
import org.jfree.chart.LegendItemCollection;
import org.jfree.chart.axis.SubCategoryAxis;
import org.jfree.chart.plot.CategoryPlot;
import org.jfree.chart.plot.PlotOrientation;
import org.jfree.chart.renderer.GroupedStackedBarRenderer;
import org.jfree.chart.axis.ValueAxis;
import org.jfree.chart.axis.CategoryAxis;
import org.jfree.chart.axis.CategoryLabelPositions;
import org.jfree.chart.axis.AxisLocation;
import org.jfree.chart.ChartUtilities;
import org.jfree.data.CategoryDataset;
import org.jfree.data.DefaultCategoryDataset;
import org.jfree.data.KeyToGroupMap;
import org.jfree.ui.ApplicationFrame;
import org.jfree.ui.GradientPaintTransformType;
import org.jfree.ui.RefineryUtilities;
import org.jfree.ui.StandardGradientPaintTransformer;
import java.util.*;
import java.io.*;

/**
 * A simple demonstration application showing how to create a stacked bar chart
 * using data from a {@link CategoryDataset}.
 */
public class RDGraphGenerator {

   private static final String mainDist = "Kilometers";
   private static final String secondaryDist = "Miles";
   private static final double unitConversion = 0.6213712;
   private static final String type0 = "Road";
   private static final String type1 = "Trail";
   private static final String type2 = "Offroad";
   private static final String type3 = "Mixed";

   private HashMap riders = new HashMap();
   private HashMap riderDistances = new HashMap();
   private HashMap riderTimes = new HashMap();

    /**
     * Creates a new demo.
     *
     * @param title  the frame title.
     */
    public RDGraphGenerator()
      throws Exception
    {
        super();
        createDataset();
        Iterator riderKeys = riders.keySet().iterator();
        while ( riderKeys.hasNext() )
        {
           String riderID = (String)riderKeys.next();
           System.out.println("Creating Dist Chart for " + riderID);
           JFreeChart chart = createDistChart(riderID);
           System.out.println("Saving Dist Chart for " + riderID);
           ChartUtilities.saveChartAsPNG(
               new File(riderID + "dist.png"), chart,
               400,300, null, true, 6);
           System.out.println("Creating Time Chart for " + riderID);
           chart = createTimeChart(riderID);
           System.out.println("Saving Time Chart for " + riderID);
           ChartUtilities.saveChartAsPNG(
               new File(riderID + "time.png"), chart,
               400,300, null, true, 6);
        }
    }

    private String monthKeyFor(int month, int year)
    {
       int smallYear = year % 100;
       String yearString = ( smallYear < 10 ) ?
                     "0" + smallYear :
                     "" + smallYear;
       switch (month)
       {
       case 1:
          return "Jan " + yearString;
       case 2:
          return "Feb " + yearString;
       case 3:
          return "Mar " + yearString;
       case 4:
          return "Apr " + yearString;
       case 5:
          return "May " + yearString;
       case 6:
          return "Jun " + yearString;
       case 7:
          return "Jul " + yearString;
       case 8:
          return "Aug " + yearString;
       case 9:
          return "Sep " + yearString;
       case 10:
          return "Oct " + yearString;
       case 11:
          return "Nov " + yearString;
       case 12:
          return "Dec " + yearString;
       }
       return "Bog " + yearString;
    }

    private DefaultCategoryDataset initializeDataset(
                        int firstMonth, int firstYear)
    {
       DefaultCategoryDataset cd = new DefaultCategoryDataset();
       //Fill in initial months
       for (int i = 0 ; i < 12 ; i ++ )
       {
          int newMonth = firstMonth + i;
          int newYear = firstYear;
          while ( newMonth > 12 )
          {
             newYear++;
             newMonth -=12;
          }
          String monthKey = monthKeyFor(newMonth, newYear);
          cd.addValue(0, "0", monthKey);
          cd.addValue(0, "1", monthKey);
          cd.addValue(0, "2", monthKey);
          cd.addValue(0, "3", monthKey);
       }
       return cd;
    }

    /**
     * Creates a sample dataset.
     *
     * @return A sample dataset.
     */
    private void createDataset() {
           
        int firstYear = 0;
        int firstMonth = 0;

        try
        {
           BufferedReader br = new BufferedReader(
                                 new InputStreamReader(
                                     System.in));
           br.readLine(); //skip header
           String line;
           while ( (line = br.readLine()) != null )
           {
            StringTokenizer st = new StringTokenizer(line, "\t");
            try
            {
               String riderID = st.nextToken();
               String firstName = st.nextToken();
               String lastName = st.nextToken();
               double tdist = Double.parseDouble(st.nextToken());
               double ttime = Double.parseDouble(st.nextToken());
               double dist0 = Double.parseDouble(st.nextToken());
               double dist1 = Double.parseDouble(st.nextToken());
               double dist2 = Double.parseDouble(st.nextToken());
               double dist3 = Double.parseDouble(st.nextToken());
               double time0 = Double.parseDouble(st.nextToken());
               double time1 = Double.parseDouble(st.nextToken());
               double time2 = Double.parseDouble(st.nextToken());
               double time3 = Double.parseDouble(st.nextToken());
               String month = st.nextToken();
               String humanMonth = st.nextToken();

               if ( firstYear == 0 )
               {
                  StringTokenizer stMonth =
                           new StringTokenizer(month, "-");
                  firstYear = (int)Double.parseDouble(
                                    stMonth.nextToken());
                  firstMonth = (int)Double.parseDouble(
                                    stMonth.nextToken());
               }

               if ( riders.get(riderID) == null )
               {
                  riders.put(riderID, firstName);
                  System.out.println("Creating " + firstName);
               }
               DefaultCategoryDataset cd = (DefaultCategoryDataset)
                              riderDistances.get(riderID);
               DefaultCategoryDataset ct = (DefaultCategoryDataset)
                              riderTimes.get(riderID);
               if ( cd == null )
               {
                  cd = initializeDataset(firstMonth, firstYear);
                  ct = initializeDataset(firstMonth, firstYear);
                  riderDistances.put(riderID, cd);
                  riderTimes.put(riderID, ct);
                  System.out.println("Creating dataset " + riderID);
               }
               System.out.println("Data for " + riderID + " - " + humanMonth);
               cd.setValue(dist0, "0" , humanMonth);
               cd.setValue(dist1, "1" , humanMonth);
               cd.setValue(dist2, "2" , humanMonth);
               cd.setValue(dist3, "3" , humanMonth);
               ct.setValue(time0, "0" , humanMonth);
               ct.setValue(time1, "1" , humanMonth);
               ct.setValue(time2, "2" , humanMonth);
               ct.setValue(time3, "3" , humanMonth);
            }
            catch ( NoSuchElementException nste)
            {
               //Ignore bogus lines.
               System.err.println("Bogus line in input file:");
               System.err.println(line);
               System.err.println("");
            }
           }
        }
        catch ( IOException ioe )
        {
           ioe.printStackTrace(System.err);
        }
    }

    /**
     * Creates a sample chart.
     *
     * @param dataset  the dataset for the chart.
     *
     * @return A sample chart.
     */
    private JFreeChart createDistChart(String riderID) {

        String riderName = (String)riders.get(riderID);
        final JFreeChart chart = ChartFactory.createStackedBarChart(
            riderName + "'s Distances",  // chart title
            "Month",                     // domain axis label
            mainDist,                // range axis label
            (CategoryDataset)riderDistances.get(riderID), // data
            PlotOrientation.VERTICAL,    // the plot orientation
            true,                        // legend
            true,                        // tooltips
            false                        // urls
        );

        GroupedStackedBarRenderer renderer = new GroupedStackedBarRenderer();
        KeyToGroupMap map = new KeyToGroupMap("G1");
        map.mapKeyToGroup("0", "G1");
        map.mapKeyToGroup("1", "G1");
        map.mapKeyToGroup("2", "G1");
        map.mapKeyToGroup("3", "G1");
        renderer.setSeriesToGroupMap(map);

        renderer.setItemMargin(0.0);
        Paint p1 = new GradientPaint(
            0.0f, 0.0f, new Color(0x22, 0x22, 0xFF), 0.0f, 0.0f, new Color(0x88, 0x88, 0xFF)
        );
        renderer.setSeriesPaint(0, p1);

        Paint p2 = new GradientPaint(
            0.0f, 0.0f, new Color(0x22, 0xFF, 0x22), 0.0f, 0.0f, new Color(0x88, 0xFF, 0x88)
        );
        renderer.setSeriesPaint(1, p2);

        Paint p3 = new GradientPaint(
            0.0f, 0.0f, new Color(0xFF, 0x22, 0x22), 0.0f, 0.0f, new Color(0xFF, 0x88, 0x88)
        );
        renderer.setSeriesPaint(2, p3);

        Paint p4 = new GradientPaint(
            0.0f, 0.0f, new Color(0xFF, 0xFF, 0x22), 0.0f, 0.0f, new Color(0xFF, 0xFF, 0x88)
        );
        renderer.setSeriesPaint(3, p4);
        renderer.setGradientPaintTransformer(
            new StandardGradientPaintTransformer(GradientPaintTransformType.HORIZONTAL)
        );



        CategoryPlot plot = (CategoryPlot) chart.getPlot();
        plot.setRenderer(renderer);
        plot.setFixedLegendItems(createLegendItems());
        ValueAxis va = (ValueAxis)plot.getRangeAxis();
        ValueAxis ova = null;
        try
        {
           ova = (ValueAxis)va.clone();
        } catch ( CloneNotSupportedException cnse ) {}
        ova.setLabel(secondaryDist);
        ova.setLowerBound(va.getLowerBound()*unitConversion);
        ova.setUpperBound(va.getUpperBound()*unitConversion);
        plot.setRangeAxis(1, ova);
        plot.setRangeAxisLocation(1, AxisLocation.TOP_OR_RIGHT);
        CategoryAxis ca = plot.getDomainAxis();
        ca.setCategoryLabelPositions(CategoryLabelPositions.DOWN_90);
        //Make around the chart transparent.
        chart.setBackgroundPaint(null);
        return chart;

    }

    private JFreeChart createTimeChart(String riderID) {

        String riderName = (String)riders.get(riderID);
        final JFreeChart chart = ChartFactory.createStackedBarChart(
            riderName + "'s Hours",  // chart title
            "Month",                     // domain axis label
            "Hours",                // range axis label
            (CategoryDataset)riderTimes.get(riderID), // data
            PlotOrientation.VERTICAL,    // the plot orientation
            true,                        // legend
            true,                        // tooltips
            false                        // urls
        );

        GroupedStackedBarRenderer renderer = new GroupedStackedBarRenderer();
        KeyToGroupMap map = new KeyToGroupMap("G1");
        map.mapKeyToGroup("0", "G1");
        map.mapKeyToGroup("1", "G1");
        map.mapKeyToGroup("2", "G1");
        map.mapKeyToGroup("3", "G1");
        renderer.setSeriesToGroupMap(map);

        renderer.setItemMargin(0.0);
        Paint p1 = new GradientPaint(
            0.0f, 0.0f, new Color(0x22, 0x22, 0xFF), 0.0f, 0.0f, new Color(0x88, 0x88, 0xFF)
        );
        renderer.setSeriesPaint(0, p1);

        Paint p2 = new GradientPaint(
            0.0f, 0.0f, new Color(0x22, 0xFF, 0x22), 0.0f, 0.0f, new Color(0x88, 0xFF, 0x88)
        );
        renderer.setSeriesPaint(1, p2);

        Paint p3 = new GradientPaint(
            0.0f, 0.0f, new Color(0xFF, 0x22, 0x22), 0.0f, 0.0f, new Color(0xFF, 0x88, 0x88)
        );
        renderer.setSeriesPaint(2, p3);

        Paint p4 = new GradientPaint(
            0.0f, 0.0f, new Color(0xFF, 0xFF, 0x22), 0.0f, 0.0f, new Color(0xFF, 0xFF, 0x88)
        );
        renderer.setSeriesPaint(3, p4);
        renderer.setGradientPaintTransformer(
            new StandardGradientPaintTransformer(GradientPaintTransformType.HORIZONTAL)
        );



        CategoryPlot plot = (CategoryPlot) chart.getPlot();
        plot.setRenderer(renderer);
        plot.setFixedLegendItems(createLegendItems());
        CategoryAxis ca = plot.getDomainAxis();
        ca.setCategoryLabelPositions(CategoryLabelPositions.DOWN_90);
        //Make around the chart transparent.
        chart.setBackgroundPaint(null);
        return chart;

    }

    /**
     * Creates the legend items for the chart.  In this case, we set them manually because we
     * only want legend items for a subset of the data series.
     *
     * @return The legend items.
     */
    private LegendItemCollection createLegendItems() {
        LegendItemCollection result = new LegendItemCollection();
        LegendItem item1 = new LegendItem(type0, new Color(0x22, 0x22, 0xFF));
        LegendItem item2 = new LegendItem(type1, new Color(0x22, 0xFF, 0x22));
        LegendItem item3 = new LegendItem(type2, new Color(0xFF, 0x22, 0x22));
        LegendItem item4 = new LegendItem(type3, new Color(0xFF, 0xFF, 0x22));
        result.add(item1);
        result.add(item2);
        result.add(item3);
        result.add(item4);
        return result;
    }

    // ****************************************************************************
    // * JFREECHART DEVELOPER GUIDE                                               *
    // * The JFreeChart Developer Guide, written by David Gilbert, is available   *
    // * to purchase from Object Refinery Limited:                                *
    // *                                                                          *
    // * http://www.object-refinery.com/jfreechart/guide.html                     *
    // *                                                                          *
    // * Sales are used to provide funding for the JFreeChart project - please    *
    // * support us so that we can continue developing free software.             *
    // ****************************************************************************

    /**
     * Starting point for the demonstration application.
     *
     * @param args  ignored.
     */
    public static void main(final String[] args)
         throws Exception
    {
        final RDGraphGenerator demo = new RDGraphGenerator();
    }

}
