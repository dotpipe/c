#include <vector>
#include <fstream>
#include <string>
#include <bitset>
#include <math.h>
#include <iostream>
#include <ctype.h>
#include <sstream>

using namespace std;

string retrieve(string& c, ofstream& out)
{
	uint8_t i_cnt = 0;
	string section_str = "";
	while (1 < c.length())
	{
		uint64_t space_bin = bitset<8>(c.front()).to_ulong();
		c = c.substr(1);
		while (space_bin > 4)
		{
			section_str += (char)(0);
			space_bin--;
		}
		section_str += (uint8_t)c.front();
		c = c.substr(1);
	}
	
	return section_str;
}

bool recollect(ifstream& in) {

	stringstream rd;
	rd << in.rdbuf();
	in.close();
	string buf = rd.str();
	rd.str("");
	uint64_t buf_len = buf.length(), bin_len = 0, buf_left = buf_len;
	uint8_t fn_s = buf.find("START---"); // 8 chars long
	uint8_t fn_t = buf.find("-----------------S",18 + fn_s + 8);
	string full_size_str = buf.substr(18, fn_s-18);
	buf = buf.substr(fn_s+8);
	uint64_t full_size = 0;
	cout << full_size_str << " " << flush;
	uint8_t fss = full_size_str.length();
	for (int8_t i = 1 ; i < fss ; i++)
	{
		full_size += (10*i) * (int)(full_size_str[i-1] - 48);
	}
	fn_t = buf.find("-----------------S");
	uint32_t filename_len = buf.find("-----------------S");
	string filename_str = buf.substr(0,fn_t);
	cout << filename_str << " " << flush;
	ofstream out {filename_str.c_str(), std::ios_base::out | std::ios_base::trunc };
	buf = buf.substr(18+fss+fn_t-6);
	uint64_t eOf = buf.find("-----------------END--------------------S");
	if (eOf != string::npos)
		buf = buf.substr(0,eOf);
	cout << "$$$$$$$" << buf.substr(eOf) << flush;
	string return_str = retrieve(buf, out);
	out.write(return_str.c_str(),return_str.length());
	return (buf.length() > 0);
} 

void collect(ifstream& in, ofstream& out, string outfilename) {

	stringstream rd;
	rd << in.rdbuf();
	in.close();
	string buf = rd.str();
	rd.str("");
	long long unsigned int p = 0, buf_len = buf.length(), bin_len = 0;
	uint64_t buf_left = buf_len;
	string bin = "";
	uint32_t buf_str_len = 6400000;
	const int MAX_BITS = 800;
	out << "-----------------S" << buf.length() << "START---" << outfilename << "-----------------S";
	for ( ; 0 < buf.length() ; ) {

		string bf = (buf.length() > buf_str_len) ? buf.substr(0, buf_str_len) : buf;
		
		while (bf.length() > 0)
		{
			string b = (bf.length() >= MAX_BITS) ? bf.substr(0,MAX_BITS) : (bf.length() > 0) ? bf : "";
			buf_left -= MAX_BITS;
			string c = "";
			for (uint8_t x : b)
			{
				c.append(bitset<8>(x).to_string());
			}
			uint64_t i = 64, j_cnt = 0, h_cnt = 0, k_cnt = 0, i_cnt = 0, l_cnt = 0;
			uint64_t ch = 0;
			while (c.length() > 0)
			{
				if (i >= (c.length()))
					i = c.length();
				// if (i > 64)
				// 	i = 8;
				ch = bitset<64>(c.substr(0,i%(c.length()+1))).to_ulong(); //(c.length()-(i%(c.length()+1)))).to_ullong();
				while (ch > 0)
				{
					if (ch%32 >= 16)
					{
						ch -= 16;
						l_cnt <<= 5;
						l_cnt += ((ch%32) << 1) + 1;
						ch >>= 5;
					}
					if (ch%32 >= 8)
					{
						ch -= 8;
						h_cnt <<= 4;
						h_cnt += ((ch%32) << 1) + 1;
						ch >>= 5;
					}
					if (ch%32 >= 4)
					{
						ch -= 4;
						i_cnt <<= 3;
						i_cnt += ((ch%32) << 1) + 1;
						ch >>= 5;
					}
					if (ch%32 >= 2)
					{
						ch -= 2;
						j_cnt <<= 2;
						j_cnt += ((ch%32) << 1) + 1;
						ch >>= 5;
					}
					if (ch%32 >= 0)
					{
						k_cnt <<= 2;
						k_cnt += ((ch%32) << 1) + 1;
						ch >>= 5;
					}
					if (j_cnt > pow(2,62) || i_cnt > pow(2,62) || h_cnt > pow(2,62) || k_cnt > pow(2,62) || l_cnt > pow(2,62))
					{
						while (j_cnt > 0 || h_cnt > 0 || i_cnt > 0 || k_cnt > 0 || l_cnt > 0)
						{
							bin += (char)((j_cnt%256)) + (char)(h_cnt%256) + (char)(i_cnt%256) + (char)(k_cnt%256) + (char)(l_cnt%256);
							i_cnt >>= 8;
							j_cnt >>= 8;
							h_cnt >>= 8;
							l_cnt >>= 8;
							k_cnt >>= 8;
						}
						l_cnt = i_cnt = h_cnt = k_cnt = j_cnt = 0;
					}
				}
				while (j_cnt > 0 || h_cnt > 0 || i_cnt > 0 || k_cnt > 0 || l_cnt > 0)
				{
					bin += (char)((j_cnt%256)) + (char)((h_cnt%256)) + (char)(i_cnt%256) + (char)(k_cnt%256) + (char)(l_cnt%256);
					i_cnt >>= 8;
					j_cnt >>= 8;
					h_cnt >>= 8;
					k_cnt >>= 8;
					l_cnt >>= 8;
				}
				l_cnt = i_cnt = h_cnt = k_cnt = j_cnt = 0;
				c = c.substr(i%(c.length()+1));
			}
			if (i_cnt > 4)
			{
				bin += (char)(i_cnt) + (char)(ch%256);
				i_cnt = 0;
				ch >>= 8;
			}
			if (bin.length() > 100000)
			{
				out << bin;
				bin_len += bin.length();
				bin.clear();
			}
			bf = (bf.length() > MAX_BITS) ? bf.substr(MAX_BITS) : "";
		}
		out << bin;
		bin_len += bin.length();
		bin.clear();
		cout << "[ [ Left:Out " << buf_left << ":" << bin_len << " | " << (bin_len/(double)(buf_len - buf_left)*100) << "% | " << (((buf_len-buf_left)/(double)(buf_len))*100) << "% ]  ]\t\t\r" << flush;
		buf = (buf.length() > buf_str_len) ? buf.substr(buf_str_len) : "";
	}
	cout << "[ [ Left:Out 0:" << bin_len << " | " << (bin_len/(double)(buf_len - buf_left)*100) << "% | " << (((buf_len-buf_left)/(double)(buf_len))*100) << "% ]  ]\t\t\r" << flush;
	bin_len += bin.length();
	out << bin;
	out << "-----------------END--------------------S";
	return;
}

int main(int argc, char *argv[]) {
	
	vector<string> filenames;
	vector<ifstream> ifstreams;
	vector<ofstream> ofstreams;
	string fname = "";

    	printf("Press, Copyright Aunk 2016\n\r: ? to continue - : ! to go back\r\n");


	std::setlocale(LC_ALL, "en_US-UTF8");
	if (string(argv[1]) == "-c") {
		
		do
		{
			cout << "\rInput File #" << (filenames.size() + 1) << ": ";
			cin >> fname;
			if (fname == "?")
				break;
			if (fname == "!" && filenames.size() > 0)
			{
				filenames.pop_back();
				continue;
			}
			filenames.push_back(fname);
		} while (fname != "?");

		printf("\nOutput File: ");
		cin >> fname;
		
		ofstream out {fname.c_str(), std::ios_base::out | std::ios_base::trunc };

		if (! out) {
			printf("You must choose a filename to continue...");
			exit(1);
		}

		if (filenames.size() == 0) {
			printf("\n\rYou must choose a filename to continue...");
			exit(1);
		}
		cout << "Data Loading..\n\r" << flush;

		for (size_t i = 0; i < filenames.size() ; i++)
		{
			ifstream in {filenames[i].c_str(), std::ios_base::in | std::ios_base::binary };
			collect(in,out, filenames[i].c_str());
			cout << "\n\r";
		}
		cout << "\n\rComplete.\r\n" << flush;

	}


	if (string(argv[1]) == "-d") {
		printf("\nInput File: ");
		
		cin >> fname;
		
		ifstream in {fname.c_str(), std::ios_base::in | std::ios_base::binary };
		cout << "Data sorting.. [" << flush; 

		(recollect(in));

	}
	
	return 0;

}
