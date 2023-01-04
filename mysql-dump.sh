#!/bin/bash

destination='./model/5. sindict.sql'
gloss_group_id=30

echo "Exporting languages..."
mysqldump -h $1 -u $2 --single-transaction -p $3 languages > "$destination" # deliberately recreate (assuming this is running post migration)
echo "Exporting accounts..."
mysqldump -h $1 -u $2 --no-create-info --single-transaction -p $3 accounts --where 'id = 1' >> "$destination"  # DO NOT CHANGE! This is an account placeholder for SinDict
echo "Exporting speech..."
mysqldump -h $1 -u $2 --no-create-info --single-transaction -p $3 speeches --where "id in(select distinct speech_id from glosses where gloss_group_id in($gloss_group_id))" >> "$destination"
echo "Exporting words..."
mysqldump -h $1 -u $2 --no-create-info --single-transaction -p $3 words --where "id in(select distinct word_id from glosses where gloss_group_id in($gloss_group_id))" >> "$destination"
echo "Exporting senses..."
mysqldump -h $1 -u $2 --no-create-info --single-transaction -p $3 senses --where "id in(select distinct sense_id from glosses where gloss_group_id in($gloss_group_id))" >> "$destination"
echo "Exporting gloss groups..."
mysqldump -h $1 -u $2 --no-create-info --single-transaction -p $3 gloss_groups --where "id in($gloss_group_id)" >> "$destination"
echo "Exporting glosses..."
mysqldump -h $1 -u $2 --no-create-info --single-transaction -p $3 glosses --where "gloss_group_id in($gloss_group_id)" >> "$destination"
echo "Exporting gloss translations..."
mysqldump -h $1 -u $2 --no-create-info --single-transaction -p $3 translations --where "gloss_id in(select distinct id from glosses where gloss_group_id in($gloss_group_id))" >> "$destination"
echo "Done!"
