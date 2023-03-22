#include <vector>
#include <fstream>
#include <string>
#include <bitset>
#include <math.h>
#include <iostream>
#include <ctype.h>
#include <sstream>

using namespace std;

void retrieve(string buf, ofstream& out)
{
	uint8_t b_bit = 0;
	string cp = "";
	uint8_t sp = 0;
	uint8_t i = 8, j = 5, i_cnt = 0, q = 0, i_back_cnt = 0;
	for (uint8_t x : buf)
	{
		if (b_bit%2 == 0)
		{
			sp = (x-4);
			b_bit++;
			continue;
		}
		else
		{
			cp.append((sp*8),'0');
			cp.append(bitset<64>(x).to_string().substr(0,i%(cp.length()+1)));
		}
		q = 0;
		while (q++ < 8)
		{
			out << (uint8_t)bitset<8>(cp.substr(0,8%(cp.length()+1))).to_ullong();
			cp = cp.substr(8%(cp.length()+1));
		}
		if (cp.length() > 0)
			out << (uint8_t)bitset<8>(cp).to_ullong();
		int t = j;
		j += i;
		i = t;
		if (i > 31)
		{
			i = 8;
			j = 5;
		}
		b_bit++;
	}
}

void recollect(ifstream& in, ofstream& out) {

	stringstream rd;
	rd << in.rdbuf();
	in.close();
	string buf = rd.str();
	rd.str("");
	uint64_t buf_len = buf.length(), bin_len = 0, buf_left = buf_len;
	while (16 < buf.length())
	{
		retrieve(buf.substr(0,16), out);
		buf = (buf.length() >= 16) ? buf.substr(16) : "";
	}
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
	const int MAX_BITS = 1024;
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
			int8_t i = 8, j = 5, i_cnt = 4, q = 0;
			uint64_t ch = 0;
			while (c.length() > 0)
			{
				
				if (i > (c.length()))
					i = c.length();
				ch = bitset<64>(c.substr(c.length()-i)).to_ullong();
				while (ch > 0)
				{
					if (ch%128 == 0)
					{
						i_cnt++;
						while (i_cnt >= 128 && i_cnt - 128 > 0)
						{
							bin += (uint8_t)(129) + (uint8_t)((128 - i_cnt)%128);
							i_cnt >>= 7;
						}
					}
					else if (ch%128 <= 4 && i_cnt == 4)
					{
						bin += (uint8_t)(128) + (uint8_t)(ch%128);
						i_cnt = 4;
					}
					else if (ch%128 <= 4 && i_cnt > 4)
					{
						bin += (uint8_t)(129) + (uint8_t)(i_cnt) + (uint8_t)(ch%128);
						i_cnt = 4;
					}
					else if (ch%128 > 4)
					{
						bin += (uint8_t)(130) + (uint8_t)(i_cnt) + (uint8_t)(ch%128);
						i_cnt = 4;
					}
					else
					{
						bin += (uint8_t)(131) + (uint8_t)(i_cnt) + (uint8_t)(ch%128);
						i_cnt = 4;
					}
					ch >>= 7;
				}
				c = c.substr(0,c.length()-i);
				int t = j;
				j += i;
				i = t;
				if (i >= (c.length()))
					c = "";
			}
			while (ch > 0)
			{
				if (ch%128 == 0)
				{
					i_cnt++;
					while (i_cnt >= 128 && i_cnt - 128 > 0)
					{
						bin += (uint8_t)(129) + (uint8_t)((128 - i_cnt)%128);
						i_cnt >>= 7;
					}
				}
				else if (ch%128 <= 4 && i_cnt == 4)
				{
					bin += (uint8_t)(128) + (uint8_t)(ch%128);
					i_cnt = 4;
				}
				else if (ch%128 <= 4 && i_cnt > 4)
				{
					bin += (uint8_t)(129) + (uint8_t)(i_cnt) + (uint8_t)(ch%128);
					i_cnt = 4;
				}
				else if (ch%128 > 4)
				{
					bin += (uint8_t)(130) + (uint8_t)(i_cnt) + (uint8_t)(ch%128);
					i_cnt = 4;
				}
				else
				{
					bin += (uint8_t)(131) + (uint8_t)(i_cnt) + (uint8_t)(ch%128);
					i_cnt = 4;
				}
				ch >>= 7;
			}
			if (bin.length() > 1000000)
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
	out << "-----------------S" << bin_len << "END---" << outfilename << "-----------------S";
	return;
}

int main(int argc, char *argv[]) {
	
	vector<string> filenames;
	vector<ifstream> ifstreams;
	vector<ofstream> ofstreams;
	string fname = "";

    	printf("Press, Copyright Aunk 2016\n\r");


	std::setlocale(LC_ALL, "en_US-UTF8");
	if (string(argv[1]) == "-c") {
		
		do
		{
			cout << "\rInput File #" << (filenames.size() + 1) << ": ";
			cin >> fname;
			if (fname == "?")
				break;
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
		printf("\nOutput File: ");
		
		cin >> fname;
		
		ifstream in {fname.c_str(), std::ios_base::in | std::ios_base::binary };

		cin >> fname;

		ofstream out {fname.c_str(), std::ios_base::out | std::ios_base::trunc };

		cout << "Data sorting.. [" << flush; 

		recollect(in,out);

	}
	
	return 0;

}
