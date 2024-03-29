#!/usr/bin/env python
import pandas as pd
import numpy as np
import datetime
import time
#import matplotlib.pyplot as plt
import re
import os
from sklearn.cluster import KMeans
from sklearn import preprocessing


def returnRes(results):
	return results


def intertime(time_list):
	num = len(time_list)
	diff = []
	#print time_list
	for i in range(0, len(time_list) - 1):
		tmp = time_list[i+1] - time_list[i]
		diff.append(tmp)
	avg_inter_time = sum(diff)/num
	return avg_inter_time


def split_ip(data, ip_regex):
	iprecv_last_two = []
	for ip in data['destination_ip']:
		temp_ip = re.findall(ip_regex, ip)
		iprecv_last_two.append(temp_ip)

	ip_recv_l1, ip_recv_l2, ip_recv_f1 = [], [], []
	for ipa in iprecv_last_two:
		ip_recv_f1.append(ipa[0][0])
		ip_recv_l1.append(ipa[0][1])
		ip_recv_l2.append(ipa[0][2])

	
	#hold the data as series using pandas  <! kudzai>
	ip_recv_l1 = pd.Series(ip_recv_l1)
	ip_recv_l2 = pd.Series(ip_recv_l2)
	ip_recv_f1 = pd.Series(ip_recv_f1)

	#Concat ip addresses
	data = pd.concat([data, ip_recv_l1, ip_recv_l2, ip_recv_f1], axis = 1)
	data = data.rename( columns = {0: 'ip_l1', 1: 'ip_l2', 2: 'ip_f1'})
	print ("IP addresses have been split up in sub domain and machine nuip_f1ip_f1mbers")
	#
	#
	#
	#

	return data

def cleanup(data):
	data = data.drop('destination_ip', axis = 1)
	data = data.drop('flags', axis = 1)
	data = data.drop('site', axis = 1)
	data = data.drop('asn', axis = 1)
	print ('Data cleaned')
	
	return data

if __name__ == '__main__':

	#Load data
	file = '/opt/lampp/htdocs/cybercate/public/python/attacks.csv'
	data = pd.read_csv(file)
	print("Data loaded")
	
	#Split destination ip
	ip_regex = r"^([0-9]{2,3}\.[a-zA-Z0-9]{5})\.([a-zA-Z0-9]{5})\.([a-zA-Z0-9]{1,3}$)"
	data = split_ip(data, ip_regex)

	#Remove columns of flags, site, asn, destination_ip
	data = cleanup(data)

	print("Data cleaning now", ".", "..", "......")
	

	#Create a dataframe for every receiver, with columns source, number of attempts,
	#average size of packet, average time difference between attempts, and avg. bytes

	#Find number of sub regions in the data set

	receivers = data.groupby('ip_f1').groups

	i = 0
	src, packets, bytes, st_time, stopper = [], [], [], [], []
	for rec in receivers:
		indexlist = receivers[rec]
	
		stopper.append(i)
		i = 0
		for index in indexlist:
			src_tmp = data['source_ip'].iloc[index]
			packets_tmp = data['num_packets'].iloc[index]
			bytes_tmp = data['num_bytes'].iloc[index]
			st_time_tmp = data['start_time'].iloc[index]

			src.append(src_tmp)
			packets.append(packets_tmp)
			bytes.append(bytes_tmp)
			st_time.append(st_time_tmp)

			i += 1

	dataset = pd.DataFrame({'source_ip': src, 'num_packets': packets, 'num_bytes': bytes,
						'start_time': st_time})
	print("Data segregated for every destination IP")
	#print(dataset.head(20))
	

	tmp_mov = 0
	stopper_mov = []
	num_recs = len(stopper)

	print ("For every destination IP, finding source IP that attempted to dock", '.', '..', '...')
	print ("For every source IP, calculating number of attempts, avg time size, and packet size")
	for j in range(0, num_recs):
		tmp_mov = sum(stopper[0:j+1])
		stopper_mov.append(tmp_mov)
	test, test2 = [], []
	lprev = 1
	s_packets, s_num_attempts, s_source, s_avg_packets, s_bytes, s_avg_bytes, time_lst, s_avg_intertime = [], [], [], [], [], [], [], []
	for j in range(0, len(stopper_mov)-1):
		split = stopper_mov[j]
		split_u = stopper_mov[j+1]
	

		recv_tmp = dataset.iloc[split:split_u, :]
		recv_source = recv_tmp.groupby('source_ip').groups
	

		#Creating the feature matrix
		for src, index_lst in recv_source.items():
			#Number of attempts by each receiver
			#print 'source:', src
			num_attempts_tmp = len(index_lst)
			s_num_attempts.append(num_attempts_tmp)
			s_source.append(src)
			#test2.append(src)
			for index in index_lst:
				#Avg. number of packets by source
				s_packets_tmp = dataset['num_packets'].iloc[index]
				s_packets.append(s_packets_tmp)
				#Avg. number of bytes by source
				s_bytes_tmp = dataset['num_bytes'].iloc[index]
				s_bytes.append(s_bytes_tmp)

				#time intervals
				time = data['start_time'].iloc[index]
				time_lst.append(time)
				#print time

				#Count to find average
				lprev = len(index_lst)

				if (index == index_lst[len(index_lst) - 1]):
					#packets
					#test.append(index)
					avg_packets = sum(s_packets)/lprev
					s_avg_packets.append(avg_packets)
					s_packets = []

					#bytes
					avg_bytes = sum(s_bytes)/lprev
					s_avg_bytes.append(avg_bytes)
					s_bytes = []

					#Time
					#print len(time_lst)
					time_sorted = sorted(time_lst)
					#print 'sorted', len(time_lst)
					avg_intertime = intertime(time_sorted)
					s_avg_intertime.append(avg_intertime)
					time_lst = []

	print('Feature matrix created!')
	print ('Number of sources:', len(s_source))
	print ('Number of destinations:', num_recs)

	features = pd.DataFrame({'source_ip': s_source, 'num_attempts': s_num_attempts,
							 'avg_num_packets': s_avg_packets, 'avg_num_bytes': s_avg_bytes,
							 'avg_intertime': s_avg_intertime})

	
	attempts = features['num_attempts'].values
	

	packets = features['avg_num_packets'].values
	

	attempts = features['avg_intertime'].values
	

	source_ip = features['source_ip'].values
	features = features.drop('source_ip', axis = 1)
	
	features = np.asarray(features, dtype = 'int')

	features_scaled = preprocessing.scale(features)
	print ('Data scaled (Z-transformed) and converted to numpy arrays for K-Means')

	print('Creating K-Means classifier to identify two clusters')
	kmeans = KMeans(n_clusters = 2, n_init = 100)

	print('fitting KMeans classifier...')
	clusters = kmeans.fit(features_scaled)

	labels = clusters.labels_
	results = pd.DataFrame({'source_ip': source_ip, 'labels': labels})
	filetime = str(datetime.datetime.now())
	export_csv = results.to_csv (r'/opt/lampp/htdocs/cybercate/public/python/system_generated_csv/result_'+filetime+'.csv', index = None, header=True)



	#add these results to array using a for each

	#print ('Malicious IP addresses identified')

	print('result_'+filetime)
	returnRes('result_'+filetime)
	#print(results)






