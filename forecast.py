# importing packages that are needed

import warnings
warnings.filterwarnings(
    "ignore",
    message="pandas only supports SQLAlchemy connectable"
)
import json
import os
from datetime import timedelta

import mysql.connector

import pandas as pd
from dotenv import load_dotenv


# loads variables from .env file
load_dotenv()

# connects to database. opens a mysql connection
def get_db_connection():
    return mysql.connector.connect(
        host=os.getenv("DB_HOST", "localhost"),
        user=os.getenv("DB_USER", "root"),
        password=os.getenv("DB_PASS", ""),
        database=os.getenv("DB_NAME", "CS_IA"),
    )


# function to collect weekly total orders data from the database to be
# processed for the forecasting later on
def fetch_weekly_orders():
    conn = get_db_connection()

    # groups the data by week starting from monday
    # counts only the orders with status "sent"
    query = """
        SELECT
            DATE(DATE_SUB(order_date, INTERVAL WEEKDAY(order_date) DAY)) AS week_start,
            COUNT(*) AS weekly_orders
        FROM orders
        WHERE status = 'sent'
          AND order_date IS NOT NULL
        GROUP BY week_start
        ORDER BY week_start;
    """
    df = pd.read_sql(query, conn)
    conn.close()
    return df







# the forecast algorithm using moving average method
# moving average: calculates the average of a fixed number of past weeks
# to predict future weeks
def build_forecast(df, weeks_ahead=8, history_limit=16):

    # if the data frame fetched earlier is empty, then sends
    # error message
    if df.empty:
        return {"error": "No sent orders to forecast."}

    # convers the week_start column to the datetime format
    
    df["week_start"] = pd.to_datetime(df["week_start"])

    # in case not sorted yet, sorts the data
    df.sort_values("week_start", inplace=True)
    df["weekly_orders"] = df["weekly_orders"].astype(int)

    # takes the history_limit parameter as the limit of old data 
    # shown on screen later on
    if history_limit and history_limit > 0:
        df = df.tail(history_limit)

    # sets the moving average window size. 
    # the window is set between 1 and 4 weeks
    window = max(1, min(4, len(df)))
    # finds mean of the previous window weeks
    baseline = df["weekly_orders"].tail(window).mean()
    last_week = df["week_start"].iloc[-1]

    # list for the future forecasted week labels
    forecast_dates = [
        (last_week + timedelta(weeks=i)).strftime("%Y-%m-%d")
        for i in range(1, weeks_ahead + 1)
    ]

    # another list for the forecasted orders
    forecast_orders = [int(round(baseline)) for _ in range(weeks_ahead)]

    # combines the two lists
    predictions = [
        {"date": date, "predicted_orders": orders}
        for date, orders in zip(forecast_dates, forecast_orders)
    ]

    

    # prepares the old data for output as well
    old_dates = df["week_start"].dt.strftime("%Y-%m-%d").tolist()
    old_orders = df["weekly_orders"].tolist()

    # creates JSON file for the output
    return {
        "success": True,
        "old_dates": old_dates,
        "old_orders": old_orders,
        "forecast_dates": forecast_dates,
        "forecast_orders": forecast_orders,
        "predictions": predictions,
        "summary": {
            "next_period_forecast": forecast_orders[0] if forecast_orders else 0,
            "total_forecast": sum(forecast_orders),
            "avg_forecast": round(sum(forecast_orders) / len(forecast_orders), 1)
            if forecast_orders
            else 0,
        },
        "meta": {
            "method": f"{window}-week moving average",
            "old_weeks_used": len(old_dates),
        },
    }




# main function to run the script
def main():
    import sys

    weeks_ahead = int(sys.argv[1]) if len(sys.argv) > 1 else 8
    history_limit = int(sys.argv[2]) if len(sys.argv) > 2 else 16

    try:
        df = fetch_weekly_orders()
    except Exception as exc:
        print(json.dumps({"error": f"Database error: {exc}"}))
        return

    if df is None or df.empty:
        print(json.dumps({"error": "No completed orders to forecast."}))
        return

    result = build_forecast(df, weeks_ahead, history_limit)
    print(json.dumps(result))



# runs main
if __name__ == "__main__":
    main()
